<?php

namespace App\Services\ApprovalTransactionService;

use App\Models\ApprovalFlowDetail;
use App\Models\ApprovalFlowTemplate;
use App\Models\ApprovalFlowUpplineConfigs;
use App\Models\ApprovalModule;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestDetail;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\Transaction;
use App\Services\ApprovalTransactionService\ApprovalTransactionService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalTransactionServiceImpl implements ApprovalTransactionService
{
    /**
     * Submit a transaction for approval.
     */
    public function submitForApproval(int $transactionId): array
    {
        try {
            $transaction = Transaction::with(['user', 'details'])->findOrFail($transactionId);

            // Check if already has pending approval
            $existingRequest = ApprovalRequest::where('reference_id', $transactionId)
                ->whereHas('module', fn($q) => $q->where('table_name', 'transactions'))
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                return [
                    'success' => false,
                    'message' => 'Transaksi sudah dalam proses approval.',
                ];
            }

            // Find module for transactions
            $module = ApprovalModule::where('table_name', 'transactions')
                ->where('is_active', true)
                ->first();

            if (!$module) {
                return [
                    'success' => false,
                    'message' => 'Approval module untuk transactions belum dikonfigurasi.',
                ];
            }

            Log::info('ApprovalTransactionService: Module found', [
                'module_id' => $module->id,
                'module_name' => $module->module_name,
            ]);

            // Find active template for this module
            $template = ApprovalFlowTemplate::where('module_id', $module->id)
                ->where('is_active', true)
                ->orderBy('priority')
                ->first();

            Log::info('ApprovalTransactionService: Template found', [
                'template_id' => $template ? $template->id : null,
                'template_name' => $template ? $template->template_name : null,
            ]);

            if (!$template) {
                return [
                    'success' => false,
                    'message' => 'Approval template belum dikonfigurasi untuk module ini.',
                ];
            }

            // Get current user's employment
            $employee = Auth::user();
            $requesterEmployment = $employee ? $employee->employment : null;

            Log::info('ApprovalTransactionService: Requester employment', [
                'employee_id' => $employee ? $employee->id : null,
                'employment_id' => $requesterEmployment ? $requesterEmployment->id : null,
            ]);

            if (!$requesterEmployment) {
                return [
                    'success' => false,
                    'message' => 'Data employment Anda tidak ditemukan.',
                ];
            }

            $requesterId = $requesterEmployment->id;

            // Get division from requester's employment
            $divisionId = $requesterEmployment->organization_id ?? null;

            // Build approval chain based on template configuration
            $approvalChain = $this->buildApprovalChain(
                $template,
                $requesterEmployment,
                $divisionId,
                $transaction->estimated_amount
            );

            if (empty($approvalChain)) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada approver yang sesuai untuk request ini.',
                ];
            }

            Log::info('Final approval chain ready for submission', [
                'transaction_id' => $transactionId,
                'template_id' => $template->id,
                'template_name' => $template->template_name,
                'use_uppline_chain' => $template->use_uppline_chain,
                'use_threshold' => $template->use_threshold,
                'division_id' => $divisionId,
                'amount' => $transaction->estimated_amount,
                'chain_count' => count($approvalChain),
                'chain_details' => $approvalChain,
            ]);

            DB::beginTransaction();

            // Create approval request
            $request = ApprovalRequest::create([
                'module_id' => $module->id,
                'reference_id' => $transactionId,
                'reference_number' => $this->generateReferenceNumber($transaction),
                'template_id' => $template->id,
                'template_snapshot' => json_encode($approvalChain),
                'status' => 'pending',
                'current_phase' => $approvalChain[0]['phase'] ?? 'uppline',
                'current_level' => 1,
                'total_levels' => count($approvalChain),
                'requester_id' => $requesterId,
                'requested_at' => now(),
            ]);

            // Create approval request details for each approver in chain
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

            // Update transaction status
            $transaction->update([
                'status' => Transaction::STATUS_IN_PROGRESS,
                'current_approval_level' => 1,
                'required_approval_levels' => count($approvalChain),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Transaksi berhasil diajukan untuk approval.',
                'data' => [
                    'request_id' => $request->id,
                    'total_approvers' => count($approvalChain),
                ],
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ApprovalTransactionService.submitForApproval', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengajukan approval: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build approval chain based on template configuration.
     */
    protected function buildApprovalChain(
        ApprovalFlowTemplate $template,
        Employment $requesterEmployment,
        ?int $divisionId,
        mixed $amount
    ): array {
        $chain = [];
        $levelSequence = 1;

        // Phase 1: Uppline Chain (if enabled)
        if ($template->use_uppline_chain) {
            $upplineApprovers = $this->resolveUplineApprovers($template->id, $requesterEmployment, $divisionId);

            Log::info('Phase 1: Uppline Chain', [
                'count' => count($upplineApprovers),
                'approvers' => $upplineApprovers,
            ]);

            foreach ($upplineApprovers as $approver) {
                $chain[] = array_merge($approver, [
                    'phase' => 'uppline',
                    'level_sequence' => $levelSequence++,
                ]);
            }
        }

        // Phase 2: Master Flow Details
        $masterFlowApprovers = $this->getMasterFlowApprovers($template, $amount);

        Log::info('Phase 2: Master Flow', [
            'count' => count($masterFlowApprovers),
            'approvers' => $masterFlowApprovers,
        ]);

        foreach ($masterFlowApprovers as $approver) {
            $chain[] = array_merge($approver, [
                'phase' => 'master_flow',
                'level_sequence' => $levelSequence++,
            ]);
        }

        Log::info('Final approval chain built', [
            'total_levels' => count($chain),
            'chain' => $chain,
        ]);

        return $chain;
    }

    /**
     * Resolve uppline chain approvers based on ApprovalFlowUpplineConfigs.
     */
    protected function resolveUplineApprovers(int $templateId, Employment $requesterEmployment, ?int $divisionId): array
    {
        // Step 1: Get uppline config (specific division first, then default)
        $upplineConfigs = $this->getUpplineConfigs($templateId, $divisionId);

        if ($upplineConfigs->isEmpty()) {
            Log::info('No uppline config found', [
                'template_id' => $templateId,
                'division_id' => $divisionId,
            ]);
            return [];
        }

        // Extract required job level names from config (ordered by step_sequence)
        $requiredJobLevels = $upplineConfigs->pluck('job_level_name')->toArray();

        Log::info('Required job levels from config', [
            'job_levels' => $requiredJobLevels,
            'division_id' => $divisionId,
        ]);

        // Step 2: Build recursive uppline chain from requester
        $upplineChain = $this->buildRecursiveUpplineChain($requesterEmployment);

        Log::info('Built uppline chain', [
            'chain' => $upplineChain,
        ]);

        // Step 3: Match uppline chain with required job levels
        $approvers = [];
        foreach ($requiredJobLevels as $jobLevelName) {
            // Find uppline with matching job_level_name
            $matchedUppline = collect($upplineChain)->first(function ($uppline) use ($jobLevelName) {
                return strtolower($uppline['job_level_name'] ?? '') === strtolower($jobLevelName);
            });

            if ($matchedUppline) {
                $approvers[] = [
                    'employment_id' => $matchedUppline['employment_id'],
                    'employment_name' => $matchedUppline['employment_name'],
                    'job_level_name' => $matchedUppline['job_level_name'],
                ];
                Log::info('Matched uppline approver', [
                    'job_level' => $jobLevelName,
                    'approver' => $matchedUppline,
                ]);
            } else {
                Log::info('No uppline found for job level, skipping', [
                    'job_level' => $jobLevelName,
                ]);
            }
        }

        return $approvers;
    }

    /**
     * Get uppline configuration for template.
     */
    protected function getUpplineConfigs(int $templateId, ?int $divisionId)
    {
        // Try specific division first
        if ($divisionId) {
            $specificConfig = ApprovalFlowUpplineConfigs::where('template_id', $templateId)
                ->where('division_id', $divisionId)
                ->orderBy('step_sequence')
                ->get();

            if ($specificConfig->isNotEmpty()) {
                Log::info('Using specific division config', [
                    'template_id' => $templateId,
                    'division_id' => $divisionId,
                ]);
                return $specificConfig;
            }
        }

        // Fall back to default config (division_id = NULL)
        $defaultConfig = ApprovalFlowUpplineConfigs::where('template_id', $templateId)
            ->whereNull('division_id')
            ->orderBy('step_sequence')
            ->get();

        Log::info('Using default config', [
            'template_id' => $templateId,
            'config_count' => $defaultConfig->count(),
        ]);

        return $defaultConfig;
    }

    /**
     * Build recursive uppline chain from requester employment.
     */
    protected function buildRecursiveUpplineChain(Employment $employment): array
    {
        $chain = [];
        $currentEmployment = $employment;
        $visitedIds = [$employment->id]; // Prevent infinite loop

        while ($currentEmployment && $currentEmployment->uppline_id) {
            // Get uppline's employee
            $upplineEmployee = Employee::find($currentEmployment->uppline_id);

            if (!$upplineEmployee || !$upplineEmployee->employment) {
                Log::info('Uppline employee or employment not found', [
                    'uppline_id' => $currentEmployment->uppline_id,
                ]);
                break;
            }

            $upplineEmployment = $upplineEmployee->employment;

            // Check for circular reference
            if (in_array($upplineEmployment->id, $visitedIds)) {
                Log::warning('Circular uppline reference detected', [
                    'employment_id' => $upplineEmployment->id,
                ]);
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

    /**
     * Get master flow approvers from ApprovalFlowDetails.
     */
    protected function getMasterFlowApprovers(ApprovalFlowTemplate $template, mixed $amount): array
    {
        $query = ApprovalFlowDetail::with('employment.employee')
            ->where('template_id', $template->id)
            ->where('is_required', true)
            ->orderBy('level_sequence');

        // Apply threshold filter if enabled
        if ($template->use_threshold && $amount > 0) {
            // Get all flow details first
            $allDetails = $query->get();

            if ($allDetails->isEmpty()) {
                Log::warning('No approval flow details found', [
                    'template_id' => $template->id,
                ]);
                return [];
            }

            // Filter based on threshold logic
            $filteredDetails = collect();
            $finalLevelFound = false;

            foreach ($allDetails as $detail) {
                // If no threshold set, include this level
                if (is_null($detail->threshold_amount)) {
                    $filteredDetails->push($detail);
                    continue;
                }

                // If amount > threshold, this level must approve (needs escalation)
                if ($amount > $detail->threshold_amount) {
                    $filteredDetails->push($detail);
                    continue;
                }

                // If amount <= threshold and we haven't found final level yet
                // This is the final sufficient level
                if (!$finalLevelFound && $amount <= $detail->threshold_amount) {
                    $filteredDetails->push($detail);
                    $finalLevelFound = true;
                    break; // Stop here, higher levels not needed
                }
            }

            // If no final level found (amount > all thresholds), include all levels
            if (!$finalLevelFound && $filteredDetails->count() === $allDetails->count()) {
                Log::info('Amount exceeds all thresholds, using all levels', [
                    'amount' => $amount,
                    'max_threshold' => $allDetails->max('threshold_amount'),
                ]);
            }

            $flowDetails = $filteredDetails;
        } else {
            // No threshold filtering, get all details
            $flowDetails = $query->get();
        }

        Log::info('Master flow details query result', [
            'template_id' => $template->id,
            'use_threshold' => $template->use_threshold,
            'amount' => $amount,
            'found_count' => $flowDetails->count(),
            'details' => $flowDetails->map(function ($d) {
                return [
                    'id' => $d->id,
                    'employment_id' => $d->employment_id,
                    'level_sequence' => $d->level_sequence,
                    'threshold_amount' => $d->threshold_amount,
                    'employee_name' => $d->employment->employee->first_name ?? 'N/A',
                ];
            }),
        ]);

        return $flowDetails->map(function ($detail) {
            $employee = $detail->employment?->employee;
            $employmentName = $employee ? $employee->first_name . ' ' . $employee->last_name : 'Unknown';

            return [
                'employment_id' => $detail->employment_id,
                'employment_name' => $employmentName,
                'threshold_amount' => $detail->threshold_amount,
                'job_level_name' => $detail->employment?->job_level_name,
            ];
        })->toArray();
    }

    /**
     * Process an approval action (approve/reject).
     */
    public function processApproval(int $requestDetailId, string $action, int $approverId, ?string $comments = null): array
    {
        try {
            $detail = ApprovalRequestDetail::with(['request.module', 'employment.employee'])
                ->findOrFail($requestDetailId);

            $request = $detail->request;

            // Validate approver
            if ($detail->employment_id != $approverId) {
                return [
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk melakukan approval ini.',
                ];
            }

            // Validate status
            if ($detail->status !== 'pending') {
                return [
                    'success' => false,
                    'message' => 'Approval ini sudah diproses sebelumnya.',
                ];
            }

            // Check if this is the correct level to approve (sequential)
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
                $result = $this->handleApprove($detail, $request, $comments);
            } elseif ($action === 'reject') {
                $result = $this->handleReject($detail, $request, $comments);
            } else {
                throw new Exception("Invalid action: {$action}");
            }

            DB::commit();

            return $result;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Process approval failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memproses approval: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle approval action.
     */
    protected function handleApprove(ApprovalRequestDetail $detail, ApprovalRequest $request, ?string $comments): array
    {
        // Update detail status
        $detail->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Check if all approvals are complete
        $pendingCount = ApprovalRequestDetail::where('request_id', $request->id)
            ->where('status', 'pending')
            ->count();

        if ($pendingCount === 0) {
            // All approved - update request and transaction
            $request->update([
                'status' => 'approved',
                'current_level' => $request->total_levels,
                'completed_at' => now(),
            ]);

            // Update the transaction
            $transaction = Transaction::find($request->reference_id);
            if ($transaction) {
                $transaction->update([
                    'status' => Transaction::STATUS_APPROVED,
                    'current_approval_level' => $request->total_levels,
                    'approval_completed_at' => now(),
                ]);
            }

            return [
                'success' => true,
                'message' => 'Transaksi telah disetujui sepenuhnya.',
                'is_fully_approved' => true,
            ];
        } else {
            // Update current level
            $request->update([
                'current_level' => $detail->level_sequence + 1,
                'status' => 'pending',
            ]);

            // Update transaction current approval level
            $transaction = Transaction::find($request->reference_id);
            if ($transaction) {
                $transaction->update([
                    'current_approval_level' => $detail->level_sequence + 1,
                ]);
            }

            return [
                'success' => true,
                'message' => 'Approval berhasil. Menunggu approver selanjutnya.',
                'is_fully_approved' => false,
                'pending_approvers' => $pendingCount,
            ];
        }
    }

    /**
     * Handle rejection action.
     */
    protected function handleReject(ApprovalRequestDetail $detail, ApprovalRequest $request, ?string $comments): array
    {
        // Update detail status
        $detail->update([
            'status' => 'rejected',
            'approved_at' => now(),
        ]);

        // Update request status
        $request->update([
            'status' => 'rejected',
            'completed_at' => now(),
        ]);

        // Mark remaining pending details as skipped
        ApprovalRequestDetail::where('request_id', $request->id)
            ->where('status', 'pending')
            ->update(['status' => 'skipped']);

        // Update the transaction
        $transaction = Transaction::find($request->reference_id);
        if ($transaction) {
            $transaction->update([
                'status' => Transaction::STATUS_REJECTED,
                'rejection_reason' => $comments,
            ]);
        }

        return [
            'success' => true,
            'message' => 'Transaksi telah ditolak.',
            'rejected_by' => $detail->employment_name,
        ];
    }

    /**
     * Get approval status for a transaction.
     */
    public function getApprovalStatus(int $transactionId): array
    {
        $transaction = Transaction::find($transactionId);

        if (!$transaction) {
            return [
                'success' => false,
                'message' => 'Transaksi tidak ditemukan.',
            ];
        }

        $request = ApprovalRequest::with('details.employment.employee')
            ->where('reference_id', $transactionId)
            ->whereHas('module', fn($q) => $q->where('table_name', 'transactions'))
            ->latest()
            ->first();

        if (!$request) {
            return [
                'success' => true,
                'data' => [
                    'transaction_id' => $transactionId,
                    'transaction_status' => $transaction->status,
                    'has_approval_request' => false,
                ],
            ];
        }

        $details = $request->details->map(function ($detail) {
            return [
                'id' => $detail->id,
                'phase' => $detail->phase,
                'level' => $detail->level_sequence,
                'approver_name' => $detail->employment_name,
                'employment_id' => $detail->employment_id,
                'status' => $detail->status,
                'approved_at' => $detail->approved_at?->format('Y-m-d H:i:s'),
            ];
        });

        return [
            'success' => true,
            'data' => [
                'transaction_id' => $transactionId,
                'transaction_status' => $transaction->status,
                'has_approval_request' => true,
                'request' => [
                    'id' => $request->id,
                    'reference_number' => $request->reference_number,
                    'status' => $request->status,
                    'current_phase' => $request->current_phase,
                    'current_level' => $request->current_level,
                    'total_levels' => $request->total_levels,
                    'requested_at' => $request->requested_at?->format('Y-m-d H:i:s'),
                    'completed_at' => $request->completed_at?->format('Y-m-d H:i:s'),
                ],
                'approvers' => $details,
            ],
        ];
    }

    /**
     * Get pending approvals for a user.
     */
    public function getPendingApprovalsForUser(int $employmentId): array
    {
        Log::info('getPendingApprovalsForUser called', ['employment_id' => $employmentId]);

        $pendingDetails = ApprovalRequestDetail::with([
            'request.module',
            'employment.employee',
        ])
            ->where('employment_id', $employmentId)
            ->where('status', 'pending')
            ->whereHas('request', fn($q) => $q->where('status', 'pending'))
            ->whereHas('request.module', fn($q) => $q->where('table_name', 'transactions'))
            ->get();

        Log::info('Found pending details for user', [
            'count' => $pendingDetails->count(),
            'details' => $pendingDetails->pluck('id')->toArray()
        ]);

        $filteredDetails = $pendingDetails->filter(function ($detail) {
                // Only return if this is the next in sequence (considering both phase and level)
                $nextPending = ApprovalRequestDetail::where('request_id', $detail->request_id)
                    ->where('status', 'pending')
                    ->orderByRaw("FIELD(phase, 'uppline', 'master_flow')")
                    ->orderBy('level_sequence')
                    ->first();

                Log::info('Checking if user is next approver', [
                    'detail_id' => $detail->id,
                    'next_pending_id' => $nextPending?->id,
                    'is_next' => $nextPending && $nextPending->id === $detail->id
                ]);

                return $nextPending && $nextPending->id === $detail->id;
            })
            ->map(function ($detail) {
                $transaction = Transaction::with(['user', 'details'])
                    ->find($detail->request->reference_id);

                return [
                    'detail_id' => $detail->id,
                    'request_id' => $detail->request_id,
                    'reference_number' => $detail->request->reference_number,
                    'level' => $detail->level_sequence,
                    'phase' => $detail->phase,
                    'total_levels' => $detail->request->total_levels,
                    'requested_at' => $detail->request->requested_at?->format('Y-m-d H:i:s'),
                    'transaction' => $transaction ? [
                        'id' => $transaction->id,
                        'purpose' => $transaction->purpose,
                        'estimated_amount' => $transaction->estimated_amount,
                        'user_name' => $transaction->user_name,
                        'transaction_date' => $transaction->transaction_date?->format('Y-m-d'),
                    ] : null,
                ];
            });

        return [
            'success' => true,
            'data' => $filteredDetails->values()->toArray(),
            'count' => $filteredDetails->count(),
        ];
    }

    /**
     * Cancel an approval request.
     */
    public function cancelApproval(int $transactionId): array
    {
        try {
            $request = ApprovalRequest::where('reference_id', $transactionId)
                ->whereHas('module', fn($q) => $q->where('table_name', 'transactions'))
                ->where('status', 'pending')
                ->first();

            if (!$request) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada approval request yang aktif untuk transaksi ini.',
                ];
            }

            DB::beginTransaction();

            // Update request status
            $request->update([
                'status' => 'cancelled',
                'completed_at' => now(),
            ]);

            // Mark all pending details as cancelled
            ApprovalRequestDetail::where('request_id', $request->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            // Update transaction status back to pending
            $transaction = Transaction::find($transactionId);
            if ($transaction) {
                $transaction->update([
                    'status' => Transaction::STATUS_PENDING,
                    'current_approval_level' => 0,
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Approval request berhasil dibatalkan.',
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Cancel approval failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membatalkan approval: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get approval timeline for display.
     */
    public function getApprovalTimeline(int $transactionId): array
    {
        try {
            $transaction = Transaction::findOrFail($transactionId);

            $request = ApprovalRequest::with(['details' => function ($q) {
                $q->orderBy('level_sequence');
            }])
                ->where('reference_id', $transactionId)
                ->whereHas('module', fn($q) => $q->where('table_name', 'transactions'))
                ->latest()
                ->first();

            $timeline = [];

            // Add submission entry
            $timeline[] = [
                'type' => 'submission',
                'status' => 'completed',
                'label' => 'Submission',
                'description' => 'Submission by ' . $transaction->user_name,
                'date' => $transaction->created_at?->format('d M Y H:i:s'),
                'badge_class' => 'bg-warning',
            ];

            if ($request) {
                foreach ($request->details as $detail) {
                    $statusClass = match ($detail->status) {
                        'approved' => 'bg-success',
                        'rejected' => 'bg-danger',
                        'pending' => 'bg-light',
                        'skipped' => 'bg-secondary',
                        default => 'bg-secondary',
                    };

                    $statusLabel = match ($detail->status) {
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'pending' => 'Pending',
                        'skipped' => 'Skipped',
                        default => 'Unknown',
                    };

                    $phaseLabel = $detail->phase === 'uppline' ? 'Uppline Chain' : 'Master Flow';

                    $timeline[] = [
                        'type' => 'approval',
                        'status' => $detail->status,
                        'phase' => $detail->phase,
                        'level' => $detail->level_sequence,
                        'label' => $phaseLabel . ' - Level ' . $detail->level_sequence,
                        'description' => $statusLabel . ' by ' . $detail->employment_name,
                        'date' => $detail->approved_at?->format('d M Y H:i:s'),
                        'badge_class' => $statusClass,
                        'approver_name' => $detail->employment_name,
                    ];
                }
            }

            return [
                'success' => true,
                'data' => $timeline,
            ];

        } catch (Exception $e) {
            Log::error('Get approval timeline failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil timeline: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate reference number for approval request.
     */
    protected function generateReferenceNumber(Transaction $transaction): string
    {
        $prefix = 'TRX-APR';
        $date = now()->format('Ymd');
        $sequence = ApprovalRequest::whereDate('created_at', now())->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }
}