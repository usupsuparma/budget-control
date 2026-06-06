<?php

namespace App\Services\BudgetSubmissionApprovalService;

use App\Models\ApprovalFlowDetail;
use App\Models\ApprovalFlowTemplate;
use App\Models\ApprovalFlowUpplineConfigs;
use App\Models\ApprovalModule;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestDetail;
use App\Models\BudgetSubmission;
use App\Models\Employment;
use App\Models\Employee;
use App\Models\WorkplanBudgetItem;
use App\Services\BudgetLedgerService\BudgetLedgerService;
use App\Services\LogService\LogService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetSubmissionApprovalServiceImpl implements BudgetSubmissionApprovalService
{
    public function __construct(
        protected LogService $logService,
        protected BudgetLedgerService $budgetLedgerService
    )
    {
    }

    public function submitForApproval(int $submissionId): array
    {
        try {
            $submission = BudgetSubmission::findOrFail($submissionId);

            if (! $this->isSubmissionPending($submission)) {
                return [
                    'success' => false,
                    'message' => 'Submission ini tidak dapat diajukan untuk approval karena sudah diproses.',
                ];
            }

            if ($submission->hasPendingApproval()) {
                return [
                    'success' => false,
                    'message' => 'Submission ini sudah dalam proses approval.',
                ];
            }

            $module = ApprovalModule::where('table_name', 'budget_submissions')
                ->where('is_active', true)
                ->first();

            if (! $module) {
                return [
                    'success' => false,
                    'message' => 'Approval module untuk budget_submissions belum dikonfigurasi.',
                ];
            }

            $template = ApprovalFlowTemplate::where('module_id', $module->id)
                ->where('is_active', true)
                ->orderBy('priority')
                ->first();

            if (! $template) {
                return [
                    'success' => false,
                    'message' => 'Approval template untuk budget_submissions belum dikonfigurasi.',
                ];
            }

            $employee = Auth::user();
            $requesterEmployment = $employee?->employment;
            $requesterId = $requesterEmployment?->id;

            if (! $requesterEmployment) {
                return [
                    'success' => false,
                    'message' => 'Data employment Anda tidak ditemukan.',
                ];
            }

            $approvalChain = $this->buildApprovalChain(
                $template,
                $requesterEmployment,
                $submission->division_id,
                $submission->estimation_amount
            );

            if (empty($approvalChain)) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada approver yang sesuai untuk request ini.',
                ];
            }

            DB::beginTransaction();

            $request = ApprovalRequest::create([
                'module_id' => $module->id,
                'reference_id' => $submission->id,
                'reference_number' => $this->generateReferenceNumber($submission),
                'template_id' => $template->id,
                'template_snapshot' => json_encode($approvalChain),
                'status' => 'pending',
                'current_phase' => $approvalChain[0]['phase'] ?? 'uppline',
                'current_level' => 1,
                'total_levels' => count($approvalChain),
                'requester_id' => $requesterId,
                'requested_at' => now(),
            ]);

            foreach ($approvalChain as $approver) {
                ApprovalRequestDetail::create([
                    'request_id' => $request->id,
                    'phase' => $approver['phase'],
                    'level_sequence' => $approver['level_sequence'],
                    'employment_id' => $approver['employment_id'],
                    'employment_name' => $approver['employment_name'],
                    'status' => 'pending',
                ]);
            }

            // Keep status as pending until approval is finalized.
            $submission->update(['status' => 0]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Submission berhasil diajukan untuk approval.',
                'data' => [
                    'request_id' => $request->id,
                    'total_approvers' => count($approvalChain),
                ],
            ];
        } catch (Exception $e) {
            DB::rollBack();

            $this->logService->create('Failed to submit budget submission for approval', [
                'submission_id' => $submissionId,
                'error' => $e->getMessage(),
            ], 'error');

            return [
                'success' => false,
                'message' => 'Gagal mengajukan approval: ' . $e->getMessage(),
            ];
        }
    }

    public function processApproval(
        int $detailId,
        string $action,
        int $approverId,
        ?string $comments = null,
        ?int $sourceBudgetAccountId = null,
        int|float|null $approvedAmount = null
    ): array
    {
        try {
            $detail = ApprovalRequestDetail::with(['request.module', 'request'])
                ->findOrFail($detailId);

            $request = $detail->request;

            if (! $request) {
                return [
                    'success' => false,
                    'message' => 'Request approval tidak ditemukan.',
                ];
            }

            if ($detail->employment_id !== $approverId) {
                return [
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk melakukan approval ini.',
                ];
            }

            if ($detail->status !== 'pending') {
                return [
                    'success' => false,
                    'message' => 'Approval ini sudah diproses sebelumnya.',
                ];
            }

            $nextPending = ApprovalRequestDetail::where('request_id', $request->id)
                ->where('status', 'pending')
                ->orderBy('level_sequence')
                ->first();

            if ($nextPending && $nextPending->id !== $detail->id) {
                return [
                    'success' => false,
                    'message' => 'Menunggu approval dari level sebelumnya.',
                ];
            }

            DB::beginTransaction();

            if ($action === 'approve') {
                $result = $this->handleApprove($detail, $request, $comments, $sourceBudgetAccountId, $approvedAmount);
            } elseif ($action === 'reject') {
                $result = $this->handleReject($detail, $request, $comments);
            } else {
                throw new Exception("Invalid action: {$action}");
            }

            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Budget submission process approval failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memproses approval: ' . $e->getMessage(),
            ];
        }
    }

    public function bulkProcessApproval(array $detailIds, string $action, int $approverId, ?string $comments = null): array
    {
        try {
            DB::beginTransaction();

            $successCount = 0;
            $failCount = 0;
            $results = [];

            foreach ($detailIds as $detailId) {
                $result = $this->processApproval((int) $detailId, $action, $approverId, $comments);

                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }

                $results[] = [
                    'detail_id' => $detailId,
                    'success' => $result['success'],
                    'message' => $result['message'],
                ];
            }

            DB::commit();

            return [
                'success' => $successCount > 0,
                'message' => "Proses bulk {$action} selesai. Berhasil: $successCount, Gagal: $failCount.",
                'data' => [
                    'results' => $results,
                    'success_count' => $successCount,
                    'fail_count' => $failCount,
                ],
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Budget submission bulk process failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memproses bulk approval: ' . $e->getMessage(),
            ];
        }
    }

    public function getApprovalTimelineForSubmission(int $submissionId): array
    {
        try {
            $submission = BudgetSubmission::with([
                'division',
                'workPlan',
                'budgetAccount',
                'sourceBudgetAccount',
                'user',
                'latestApprovalRequest.details',
                'latestApprovalRequest.module',
            ])->findOrFail($submissionId);

            $request = $submission->latestApprovalRequest;
            if (! $request) {
                return [
                    'success' => false,
                    'message' => 'Submission ini belum memiliki approval request.',
                ];
            }

            $timeline = $this->buildTimeline($request->details);
            $currentPending = collect($timeline)->firstWhere('status', 'pending');

            return [
                'success' => true,
                'data' => [
                    'request_id' => $request->id,
                    'reference_number' => $request->reference_number,
                    'status' => $request->status,
                    'current_level' => $request->current_level,
                    'total_levels' => $request->total_levels,
                    'requested_at' => $request->requested_at?->format('Y-m-d H:i:s'),
                    'completed_at' => $request->completed_at?->format('Y-m-d H:i:s'),
                    'requester_name' => Employment::with('employee')->find($request->requester_id)?->employee?->name ?? '-',
                    'current_approver' => $currentPending['employment_name'] ?? '-',
                    'timeline' => $timeline,
                    'submission' => $this->buildSubmissionPayload($submission),
                ],
            ];
        } catch (Exception $e) {
            $this->logService->create('Failed to get budget submission approval timeline', [
                'submission_id' => $submissionId,
                'error' => $e->getMessage(),
            ], 'error');

            return [
                'success' => false,
                'message' => 'Gagal memuat approval timeline: ' . $e->getMessage(),
            ];
        }
    }

    public function getPendingApprovalsForUser(int $employmentId): array
    {
        try {
            $pendingDetails = ApprovalRequestDetail::with([
                'request.module',
                'request.details',
                'employment.employee',
            ])
                ->where('employment_id', $employmentId)
                ->where('status', 'pending')
                ->whereHas('request', fn ($q) => $q->where('status', 'pending'))
                ->whereHas('request.module', fn ($q) => $q->where('table_name', 'budget_submissions'))
                ->get()
                ->filter(function (ApprovalRequestDetail $detail) {
                    $nextPending = ApprovalRequestDetail::where('request_id', $detail->request_id)
                        ->where('status', 'pending')
                        ->orderBy('level_sequence')
                        ->first();

                    return $nextPending && $nextPending->id === $detail->id;
                })
                ->map(function (ApprovalRequestDetail $detail) {
                    $submission = BudgetSubmission::with(['division', 'workPlan', 'budgetAccount', 'sourceBudgetAccount', 'user'])
                        ->find($detail->request->reference_id);

                    if (! $submission) {
                        return null;
                    }

                    $timeline = $this->buildTimeline($detail->request->details);

                    return [
                        'detail_id' => $detail->id,
                        'request_id' => $detail->request_id,
                        'reference_number' => $detail->request->reference_number,
                        'level' => $detail->level_sequence,
                        'total_levels' => $detail->request->total_levels,
                        'requested_at' => $detail->request->requested_at?->format('Y-m-d H:i:s'),
                        'requester_name' => Employment::with('employee')->find($detail->request->requester_id)?->employee?->name ?? '-',
                        'timeline' => $timeline,
                        'submission' => $this->buildSubmissionPayload($submission, $detail),
                    ];
                })
                ->values();

            return [
                'success' => true,
                'data' => $pendingDetails->toArray(),
                'count' => $pendingDetails->count(),
            ];
        } catch (Exception $e) {
            $this->logService->create('Failed to get pending approval submissions', [
                'employment_id' => $employmentId,
                'error' => $e->getMessage(),
            ], 'error');

            return [
                'success' => false,
                'message' => 'Gagal memuat pending approvals: ' . $e->getMessage(),
                'data' => [],
                'count' => 0,
            ];
        }
    }

    public function getApprovedApprovalsForUser(int $employmentId): array
    {
        try {
            $approvedDetails = ApprovalRequestDetail::with([
                'request.module',
                'request.details',
                'employment.employee',
            ])
                ->where('employment_id', $employmentId)
                ->where('status', 'approved')
                ->whereHas('request', fn ($q) => $q->whereHas('module', fn ($q2) => $q2->where('table_name', 'budget_submissions')))
                ->orderByDesc('approved_at')
                ->orderByDesc('id')
                ->get()
                ->map(function (ApprovalRequestDetail $detail) {
                    $submission = BudgetSubmission::with(['division', 'workPlan', 'budgetAccount', 'sourceBudgetAccount', 'user'])
                        ->find($detail->request->reference_id);

                    if (! $submission) {
                        return null;
                    }

                    $timeline = $this->buildTimeline($detail->request->details);

                    return [
                        'detail_id' => $detail->id,
                        'request_id' => $detail->request_id,
                        'reference_number' => $detail->request->reference_number,
                        'level' => $detail->level_sequence,
                        'total_levels' => $detail->request->total_levels,
                        'approved_at' => $detail->approved_at?->format('Y-m-d H:i:s'),
                        'requester_name' => Employment::with('employee')->find($detail->request->requester_id)?->employee?->name ?? '-',
                        'timeline' => $timeline,
                        'submission' => $this->buildSubmissionPayload($submission, $detail),
                    ];
                })
                ->filter()
                ->values();

            return [
                'success' => true,
                'data' => $approvedDetails->toArray(),
                'count' => $approvedDetails->count(),
            ];
        } catch (Exception $e) {
            $this->logService->create('Failed to get approved approval submissions', [
                'employment_id' => $employmentId,
                'error' => $e->getMessage(),
            ], 'error');

            return [
                'success' => false,
                'message' => 'Gagal memuat approval history: ' . $e->getMessage(),
                'data' => [],
                'count' => 0,
            ];
        }
    }

    protected function isSubmissionPending(BudgetSubmission $submission): bool
    {
        return $submission->status === 0;
    }

    protected function handleApprove(
        ApprovalRequestDetail $detail,
        ApprovalRequest $request,
        ?string $comments,
        ?int $sourceBudgetAccountId = null,
        int|float|null $approvedAmount = null
    ): array
    {
        $submission = BudgetSubmission::whereKey($request->reference_id)
            ->lockForUpdate()
            ->first();

        if ($submission && $this->requiresFinalBudgetMovementApproval($submission, $detail)) {
            $this->applyFinalApprovedAmount($submission, $detail, $approvedAmount);

            if ($this->requiresFinalAddBudgetSourceSelection($submission, $detail)) {
                $this->applyFinalAddBudgetSource($submission, $sourceBudgetAccountId);
            }
        }

        $detail->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $pendingCount = ApprovalRequestDetail::where('request_id', $request->id)
            ->where('status', 'pending')
            ->count();

        if ($pendingCount === 0) {
            $request->update([
                'status' => 'approved',
                'current_level' => $request->total_levels,
                'completed_at' => now(),
            ]);

            if ($submission) {
                $mutationResult = $this->budgetLedgerService->recordBudgetSubmissionMovement($submission->id);

                if (! $mutationResult['success']) {
                    throw new Exception($mutationResult['message']);
                }

                $submission->update(['status' => 1]);
            }

            return [
                'success' => true,
                'message' => 'Submission telah disetujui sepenuhnya.',
                'is_fully_approved' => true,
            ];
        }

        $request->update([
            'current_level' => $detail->level_sequence + 1,
            'status' => 'pending',
        ]);

        return [
            'success' => true,
            'message' => 'Approval berhasil. Menunggu approver selanjutnya.',
            'is_fully_approved' => false,
            'pending_approvers' => $pendingCount,
        ];
    }

    protected function handleReject(ApprovalRequestDetail $detail, ApprovalRequest $request, ?string $comments): array
    {
        $submission = BudgetSubmission::find($request->reference_id);

        $detail->update([
            'status' => 'rejected',
            'approved_at' => now(),
        ]);

        $request->update([
            'status' => 'rejected',
            'completed_at' => now(),
        ]);

        ApprovalRequestDetail::where('request_id', $request->id)
            ->where('status', 'pending')
            ->update(['status' => 'skipped']);

        if ($submission) {
            $submission->update(['status' => 2]);
        }

        if (! empty($comments)) {
            $this->logService->create('Budget submission rejection comment', [
                'submission_id' => $submission?->id,
                'detail_id' => $detail->id,
                'comments' => $comments,
            ], 'info');
        }

        return [
            'success' => true,
            'message' => 'Submission telah ditolak.',
            'rejected_by' => $detail->employment_name,
        ];
    }

    protected function buildApprovalChain(
        ApprovalFlowTemplate $template,
        Employment $requesterEmployment,
        ?int $divisionId,
        mixed $amount
    ): array {
        $chain = [];
        $levelSequence = 1;

        if ($template->use_uppline_chain) {
            $upplineApprovers = $this->resolveUplineApprovers(
                $template,
                $requesterEmployment,
                $divisionId,
                $amount
            );

            foreach ($upplineApprovers as $approver) {
                $chain[] = array_merge($approver, [
                    'phase' => 'uppline',
                    'level_sequence' => $levelSequence++,
                ]);
            }
        }

        $masterFlowApprovers = $this->getMasterFlowApprovers($template, $amount);

        foreach ($masterFlowApprovers as $approver) {
            $chain[] = array_merge($approver, [
                'phase' => 'master_flow',
                'level_sequence' => $levelSequence++,
            ]);
        }

        return $chain;
    }

    protected function resolveUplineApprovers(
        ApprovalFlowTemplate $template,
        Employment $requesterEmployment,
        ?int $divisionId,
        mixed $amount
    ): array {
        $upplineConfigs = $this->getUpplineConfigs($template->id, $divisionId);

        if ($upplineConfigs->isEmpty()) {
            return [];
        }

        $upplineChain = $this->buildRecursiveUpplineChain($requesterEmployment);

        $approvers = [];
        foreach ($upplineConfigs as $config) {
            $jobLevelName = $config->job_level_name;
            $thresholdAmount = $config->threshold_amount ?? 0;

            if ($template->use_threshold && $amount < $thresholdAmount) {
                continue;
            }

            $matchedUppline = collect($upplineChain)->first(function ($uppline) use ($jobLevelName) {
                return strtolower((string) $uppline['job_level_name']) === strtolower((string) $jobLevelName);
            });

            if ($matchedUppline) {
                $approvers[] = [
                    'employment_id' => $matchedUppline['employment_id'],
                    'employment_name' => $matchedUppline['employment_name'],
                    'job_level_name' => $matchedUppline['job_level_name'],
                ];
            }
        }

        return $approvers;
    }

    protected function getUpplineConfigs(int $templateId, ?int $divisionId)
    {
        if ($divisionId) {
            $specificConfig = ApprovalFlowUpplineConfigs::where('template_id', $templateId)
                ->where('division_id', $divisionId)
                ->orderBy('step_sequence')
                ->get();

            if ($specificConfig->isNotEmpty()) {
                return $specificConfig;
            }
        }

        return ApprovalFlowUpplineConfigs::where('template_id', $templateId)
            ->whereNull('division_id')
            ->orderBy('step_sequence')
            ->get();
    }

    protected function buildRecursiveUpplineChain(Employment $employment): array
    {
        $chain = [];
        $currentEmployment = $employment;
        $visitedIds = [$employment->id];

        while ($currentEmployment && $currentEmployment->uppline_id) {
            $upplineEmployee = Employee::find($currentEmployment->uppline_id);

            if (! $upplineEmployee || ! $upplineEmployee->employment) {
                break;
            }

            $upplineEmployment = $upplineEmployee->employment;

            if (in_array($upplineEmployment->id, $visitedIds, true)) {
                break;
            }

            $visitedIds[] = $upplineEmployment->id;

            $chain[] = [
                'employment_id' => $upplineEmployment->id,
                'employment_name' => $upplineEmployee->first_name . ' ' . $upplineEmployee->last_name,
                'job_level_name' => $upplineEmployment->job_level_name,
                'job_level_id' => $upplineEmployment->job_level_id,
            ];

            $currentEmployment = $upplineEmployment;
        }

        return $chain;
    }

    protected function getMasterFlowApprovers(ApprovalFlowTemplate $template, mixed $amount): array
    {
        $query = ApprovalFlowDetail::with('employment.employee')
            ->where('template_id', $template->id)
            ->where('is_required', true)
            ->orderBy('level_sequence');

        if ($template->use_threshold && $amount > 0) {
            $allDetails = $query->get();

            if ($allDetails->isEmpty()) {
                return [];
            }

            $filteredDetails = new Collection();
            $finalLevelFound = false;

            foreach ($allDetails as $detail) {
                if (is_null($detail->threshold_amount)) {
                    $filteredDetails->push($detail);
                    continue;
                }

                if ($amount > $detail->threshold_amount) {
                    $filteredDetails->push($detail);
                    continue;
                }

                if (! $finalLevelFound && $amount <= $detail->threshold_amount) {
                    $filteredDetails->push($detail);
                    $finalLevelFound = true;
                    break;
                }
            }

            $flowDetails = $filteredDetails;
        } else {
            $flowDetails = $query->get();
        }

        return $flowDetails->map(function ($detail) {
            $employee = $detail->employment?->employee;
            $employmentName = $employee ? trim(($employee->first_name ?? '-') . ' ' . ($employee->last_name ?? '')) : 'Unknown';

            return [
                'employment_id' => $detail->employment_id,
                'employment_name' => $employmentName,
                'threshold_amount' => $detail->threshold_amount,
                'job_level_name' => $detail->employment?->job_level_name,
            ];
        })->toArray();
    }

    protected function buildTimeline($details): array
    {
        if (! $details) {
            return [];
        }

        return $details
            ->sortBy('level_sequence')
            ->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'level_sequence' => $detail->level_sequence,
                    'phase' => $detail->phase,
                    'employment_name' => $detail->employment_name,
                    'status' => $detail->status,
                    'approved_at' => $detail->approved_at?->format('Y-m-d H:i:s'),
                ];
            })
            ->values()
            ->toArray();
    }

    protected function buildSubmissionPayload(BudgetSubmission $submission, ?ApprovalRequestDetail $detail = null): array
    {
        return [
            'id' => $submission->id,
            'submission_date' => $submission->submission_date?->format('Y-m-d'),
            'division_id' => $submission->division_id,
            'division_name' => $submission->division?->name,
            'type' => $submission->type,
            'type_label' => $submission->type_label,
            'work_plan_id' => $submission->work_plan_id,
            'work_plan_activity' => $submission->workPlan?->activity ?? '-',
            'budget_account_id' => $submission->budget_account_id,
            'budget_account' => $submission->budget_account_label,
            'source_budget_account_id' => $submission->source_budget_account_id,
            'source_budget_account' => $submission->source_budget_account_label,
            'description' => $submission->description,
            'estimation_amount' => (int) $submission->estimation_amount,
            'approved_amount' => $submission->approved_amount ? (int) $submission->approved_amount : null,
            'approved_movement_amount' => $submission->approved_movement_amount,
            'has_approved_amount_adjustment' => $submission->has_approved_amount_adjustment,
            'approved_amount_changed_at' => $submission->approved_amount_changed_at?->format('Y-m-d H:i:s'),
            'requires_source_budget_selection' => $detail
                ? $this->requiresFinalAddBudgetSourceSelection($submission, $detail)
                : false,
            'requires_approved_amount_input' => $detail
                ? $this->requiresFinalBudgetMovementApproval($submission, $detail)
                : false,
        ];
    }

    protected function requiresFinalBudgetMovementApproval(
        BudgetSubmission $submission,
        ApprovalRequestDetail $detail
    ): bool {
        if (! in_array($submission->type, ['add', 'relocation'], true)) {
            return false;
        }

        return $this->isFinalApprovalLevel($detail) || $this->isFinalPendingApproval($detail);
    }

    protected function requiresFinalAddBudgetSourceSelection(
        BudgetSubmission $submission,
        ApprovalRequestDetail $detail
    ): bool {
        if ($submission->type !== 'add' || ! empty($submission->source_budget_account_id)) {
            return false;
        }

        return $this->isFinalApprovalLevel($detail) || $this->isFinalPendingApproval($detail);
    }

    protected function isFinalApprovalLevel(ApprovalRequestDetail $detail): bool
    {
        if ($detail->status !== 'pending') {
            return false;
        }

        $totalLevels = (int) ($detail->request?->total_levels ?? 0);
        if ($totalLevels <= 0) {
            return false;
        }

        return (int) $detail->level_sequence >= $totalLevels;
    }

    protected function isFinalPendingApproval(ApprovalRequestDetail $detail): bool
    {
        if ($detail->status !== 'pending') {
            return false;
        }

        return ApprovalRequestDetail::where('request_id', $detail->request_id)
            ->where('status', 'pending')
            ->count() === 1;
    }

    protected function applyFinalAddBudgetSource(BudgetSubmission $submission, ?int $sourceBudgetAccountId): void
    {
        if (! $sourceBudgetAccountId) {
            throw new Exception('Budget sumber wajib dipilih pada approval terakhir untuk Add Budget.');
        }

        if ((int) $sourceBudgetAccountId === (int) $submission->budget_account_id) {
            throw new Exception('Budget item sumber dan tujuan Add Budget tidak boleh sama.');
        }

        $sourceBudgetItem = WorkplanBudgetItem::approved()
            ->where('id', $sourceBudgetAccountId)
            ->lockForUpdate()
            ->first();

        if (! $sourceBudgetItem) {
            throw new Exception('Budget item sumber tidak ditemukan atau belum approved.');
        }

        $balanceResult = $this->budgetLedgerService->getBudgetBalance($sourceBudgetItem->id);
        if (! $balanceResult['success']) {
            throw new Exception($balanceResult['message']);
        }

        $amount = (float) $submission->approved_movement_amount;
        $currentBalance = (float) $balanceResult['data']['current_balance'];
        if ($amount > $currentBalance) {
            throw new Exception(
                'Saldo budget sumber tidak mencukupi. Saldo tersedia Rp '
                . number_format($currentBalance, 0, ',', '.')
                . ', nilai Add Budget Rp '
                . number_format($amount, 0, ',', '.')
                . '.'
            );
        }

        $submission->update(['source_budget_account_id' => $sourceBudgetItem->id]);
        $submission->setRelation('sourceBudgetAccount', $sourceBudgetItem);
    }

    protected function applyFinalApprovedAmount(
        BudgetSubmission $submission,
        ApprovalRequestDetail $detail,
        int|float|null $approvedAmount
    ): void {
        $amount = $approvedAmount ?? $submission->estimation_amount;
        $amount = (int) round((float) $amount);

        if ($amount <= 0) {
            throw new Exception('Nominal approved harus lebih dari 0.');
        }

        $isAdjusted = $amount !== (int) $submission->estimation_amount;

        $submission->update([
            'approved_amount' => $amount,
            'approved_amount_changed_by' => $isAdjusted ? $detail->employment_id : null,
            'approved_amount_changed_at' => $isAdjusted ? now() : null,
        ]);

        $submission->approved_amount = $amount;
    }

    protected function generateReferenceNumber(BudgetSubmission $submission): string
    {
        $prefix = 'BS-APR';
        $date = now()->format('Ymd');
        $sequence = ApprovalRequest::whereDate('created_at', now())->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }
}
