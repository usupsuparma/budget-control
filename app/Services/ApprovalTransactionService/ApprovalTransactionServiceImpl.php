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
use App\Services\BudgetLedgerService\BudgetLedgerService;
use App\Services\LogService\LogService;
use App\Services\NotificationService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalTransactionServiceImpl implements ApprovalTransactionService
{
    protected BudgetLedgerService $budgetLedgerService;
    protected LogService $logService;
    protected NotificationService $notificationService;

    public function __construct(BudgetLedgerService $budgetLedgerService, LogService $logService, NotificationService $notificationService)
    {
        $this->budgetLedgerService = $budgetLedgerService;
        $this->logService = $logService;
        $this->notificationService = $notificationService;
    }
    /**
     * Submit a transaction for approval.
     */
    public function submitForApproval(int $transactionId): array
    {
        try {
            $transaction = Transaction::with(['user', 'details'])->findOrFail($transactionId);
            $this->logService->create("Submitting transaction for approval", [
                'transaction_id' => $transactionId,
                'user_id' => $transaction->user_id,
                'estimated_amount' => $transaction->estimated_amount,
            ], 'info');

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

            $this->logService->create("ApprovalTransactionService: Module found", [
                'module_id' => $module->id,
                'module_name' => $module->module_name,
            ]);

            // Find active template for this module
            $template = ApprovalFlowTemplate::where('module_id', $module->id)
                ->where('is_active', true)
                ->orderBy('priority')
                ->first();

            $this->logService->create("ApprovalTransactionService: Template found", [
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

            $this->logService->create("ApprovalTransactionService: Requester employment", [
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

            $this->logService->create("Final approval chain ready for submission", [
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

            // Update transaction status - set status_approval to pending (waiting for first approval)
            $transaction->update([
                'status' => Transaction::STATUS_PROGRESS,
                'status_approval' => Transaction::APPROVAL_STATUS_PENDING,
                'current_approval_level' => 1,
                'required_approval_levels' => count($approvalChain),
            ]);

            DB::commit();

            // Notify first approver
            $firstApprover = $approvalChain[0];
            $this->notificationService->sendToEmployment(
                $firstApprover['employment_id'],
                'approval',
                'Permintaan Approval Transaksi',
                "Ada permintaan approval baru untuk Transaksi: {$transaction->purpose} senilai " . number_format($transaction->estimated_amount, 0, ',', '.')
            );

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
            $this->logService->create("Error in submitForApproval", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 'error');

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
            $upplineApprovers = $this->resolveUplineApprovers($template, $requesterEmployment, $divisionId, $amount);

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
     * Supports threshold-based filtering: only include approvers where amount >= threshold_amount.
     */
    protected function resolveUplineApprovers(ApprovalFlowTemplate $template, Employment $requesterEmployment, ?int $divisionId, mixed $amount): array
    {
        // Step 1: Get uppline config (specific division first, then default)
        $upplineConfigs = $this->getUpplineConfigs($template->id, $divisionId);

        if ($upplineConfigs->isEmpty()) {
            Log::info('No uppline config found', [
                'template_id' => $template->id,
                'division_id' => $divisionId,
            ]);
            return [];
        }

        // Step 2: Build recursive uppline chain from requester
        $upplineChain = $this->buildRecursiveUpplineChain($requesterEmployment);

        Log::info('Built uppline chain', [
            'chain' => $upplineChain,
        ]);

        // Step 3: Match uppline chain with required job levels (with threshold filtering)
        $approvers = [];
        foreach ($upplineConfigs as $config) {
            $jobLevelName = $config->job_level_name;
            $thresholdAmount = $config->threshold_amount ?? 0;

            // Check threshold if use_threshold is enabled
            if ($template->use_threshold && $amount < $thresholdAmount) {
                Log::info('Skipping uppline approver due to threshold', [
                    'job_level' => $jobLevelName,
                    'threshold_amount' => $thresholdAmount,
                    'request_amount' => $amount,
                ]);
                continue;
            }

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
                    'threshold_checked' => $template->use_threshold,
                    'threshold_amount' => $thresholdAmount,
                    'request_amount' => $amount,
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
        $this->logService->create("ApprovalTransactionServiceImpl.handleApprove", [
            'detail_id' => $detail->id,
            'request_id' => $request->id,
            'approver_id' => $detail->employment_id,
            'reference_id' => $request->reference_id,
        ], 'info');

        $detail->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Check if all approvals are complete
        $pendingCount = ApprovalRequestDetail::where('request_id', $request->id)
            ->where('status', 'pending')
            ->count();

        // Get transaction for status update
        $transaction = Transaction::find($request->reference_id);

        if ($pendingCount === 0) {
            // === BUDGET LEDGER: Validate budget sufficiency before final approval ===
            $budgetValidation = $this->budgetLedgerService->validateBudgetSufficiency($request->reference_id);
            if (!$budgetValidation['success']) {
                // Rollback approval detail status
                $detail->update(['status' => 'pending', 'approved_at' => null]);
                $this->logService->create("Budget validation failed on final approval", [
                    'transaction_id' => $transaction ? $transaction->id : null,
                    'request_id' => $request->id,
                    'message' => $budgetValidation['message'],
                    'insufficient_items' => $budgetValidation['insufficient_items'] ?? [],
                ], 'warning');
                return [
                    'success' => false,
                    'message' => 'Saldo anggaran tidak mencukupi: ' . $budgetValidation['message'],
                    'insufficient_items' => $budgetValidation['insufficient_items'] ?? [],
                ];
            }

            // All approved - update request and transaction
            $request->update([
                'status' => 'approved',
                'current_level' => $request->total_levels,
                'completed_at' => now(),
            ]);

            // Update the transaction - fully approved and ready for disbursement (PAID)
            if ($transaction) {
                $transaction->update([
                    'status' => Transaction::STATUS_PAID, // Status 3 - Ready for disbursement
                    'status_approval' => Transaction::APPROVAL_STATUS_APPROVED,
                    'current_approval_level' => $request->total_levels,
                    'approval_completed_at' => now(),
                ]);

                // === BUDGET LEDGER: Phase 1 - Record Cash Advance debit mutations ===
                $mutationResult = $this->budgetLedgerService->recordCashAdvanceMutations($transaction->id);
                if (!$mutationResult['success']) {
                    $this->logService->create('Failed to record cash advance mutations (non-blocking)', [
                        'transaction_id' => $transaction->id,
                        'error' => $mutationResult['message'],
                    ], 'warning');
                }

                // Notify requester that transaction is fully approved
                $this->notificationService->sendToEmployment(
                    $request->requester_id,
                    'approval',
                    'Transaksi Disetujui',
                    "Transaksi Anda: {$transaction->purpose} telah disetujui sepenuhnya."
                );
            }

            return [
                'success' => true,
                'message' => 'Transaksi telah disetujui sepenuhnya dan siap untuk di-disbursed.',
                'is_fully_approved' => true,
            ];
        } else {
            // Update current level
            $request->update([
                'current_level' => $detail->level_sequence + 1,
                'status' => 'pending',
            ]);

            // Update transaction - in progress (at least one approved, more pending)
            if ($transaction) {
                $transaction->update([
                    'current_approval_level' => $detail->level_sequence + 1,
                    'status_approval' => Transaction::APPROVAL_STATUS_IN_PROGRESS,
                ]);
                $this->logService->create('Transaction approval in progress', [
                    'transaction_id' => $transaction->id,
                    'current_approval_level' => $detail->level_sequence + 1,
                ], 'info');

                // Notify next approver
                $nextApprover = ApprovalRequestDetail::where('request_id', $request->id)
                    ->where('status', 'pending')
                    ->orderBy('level_sequence')
                    ->first();
                
                if ($nextApprover) {
                    $this->notificationService->sendToEmployment(
                        $nextApprover->employment_id,
                        'approval',
                        'Permintaan Approval Transaksi',
                        "Ada permintaan approval baru untuk Transaksi: {$transaction->purpose}"
                    );
                }
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

        // Update the transaction - rejected
        $transaction = Transaction::find($request->reference_id);
        if ($transaction) {
            $transaction->update([
                'status' => Transaction::STATUS_REJECTED,
                'status_approval' => Transaction::APPROVAL_STATUS_REJECTED,
                'rejection_reason' => $comments,
            ]);

            // Notify requester that transaction is rejected
            $this->notificationService->sendToEmployment(
                $request->requester_id,
                'approval',
                'Transaksi Ditolak',
                "Transaksi Anda: {$transaction->purpose} telah ditolak oleh {$detail->employment_name}."
            );
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

    /**     * Get approval items by status for a user.
     */
    public function getApprovalItemsByStatus(int $employmentId, string $status, array $filters = []): array
    {
        try {
            $year = $filters['year'] ?? null;
            $search = $filters['search'] ?? null;
            $page = $filters['page'] ?? 1;
            $perPage = $filters['per_page'] ?? 10;

            Log::info('getApprovalItemsByStatus called', [
                'employment_id' => $employmentId,
                'status' => $status,
                'filters' => $filters
            ]);

            // Build query
            $query = ApprovalRequestDetail::with([
                'request.transaction.user',
                'request.transaction.details',
                'employment.employee'
            ])
            ->where('employment_id', $employmentId)
            ->whereHas('request.module', fn($q) => $q->where('table_name', 'transactions'));

            // Filter by status
            if ($status === 'pending') {
                $query->where('status', 'pending')
                    ->whereHas('request', fn($q) => $q->where('status', 'pending'));
            } elseif ($status === 'approved') {
                $query->where('status', 'approved');
            } elseif ($status === 'rejected') {
                $query->where('status', 'rejected');
            }

            // Filter by year
            if ($year && $year !== 'all') {
                $query->whereHas('request.transaction', function ($q) use ($year) {
                    $q->whereYear('transaction_date', $year);
                });
            }

            // Search filter
            if ($search) {
                $query->whereHas('request.transaction', function ($q) use ($search) {
                    $q->where('purpose', 'like', "%{$search}%")
                        ->orWhere('transaction_number', 'like', "%{$search}%");
                });
            }

            // Order by latest
            $query->orderBy('created_at', 'desc');

            // Get total count for pagination
            $total = $query->count();
            $lastPage = ceil($total / $perPage);
            $from = (($page - 1) * $perPage) + 1;
            $to = min($page * $perPage, $total);

            // Apply pagination
            $approvalDetails = $query->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            Log::info('Query results', [
                'total' => $total,
                'count' => $approvalDetails->count()
            ]);

            // Transform data
            $data = $approvalDetails->map(function ($detail) use ($employmentId, $status) {
                $transaction = $detail->request->transaction ?? null;
                
                if (!$transaction) {
                    return null;
                }

                // For pending items, check if this user is the next approver
                $canApprove = false;
                if ($status === 'pending') {
                    $nextPending = ApprovalRequestDetail::where('request_id', $detail->request_id)
                        ->where('status', 'pending')
                        ->orderByRaw("FIELD(phase, 'uppline', 'master_flow')")
                        ->orderBy('level_sequence')
                        ->first();
                    
                    $canApprove = $nextPending && $nextPending->id === $detail->id;
                }

                return [
                    'id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'transaction_date' => $transaction->transaction_date,
                    'user_name' => $transaction->user->name ?? 'N/A',
                    'purpose' => $transaction->purpose,
                    'urgency' => $transaction->urgency,
                    'estimated_amount' => $transaction->estimated_amount,
                    'status' => $transaction->status,
                    'can_approve' => $canApprove,
                    'can_approve_detail_id' => $detail->id,
                    'approval_status' => $detail->status,
                    'approved_at' => $detail->approved_at,
                    'phase' => $detail->phase,
                    'level_sequence' => $detail->level_sequence
                ];
            })->filter()->values();

            return [
                'success' => true,
                'data' => [
                    'data' => $data->toArray(),
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'per_page' => $perPage,
                    'total' => $total,
                    'from' => $total > 0 ? $from : 0,
                    'to' => $total > 0 ? $to : 0,
                    'prev_page_url' => $page > 1 ? url()->current() . '?page=' . ($page - 1) : null,
                    'next_page_url' => $page < $lastPage ? url()->current() . '?page=' . ($page + 1) : null
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error in getApprovalItemsByStatus: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Error fetching approval items: ' . $e->getMessage(),
                'data' => [
                    'data' => [],
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $perPage ?? 10,
                    'total' => 0,
                    'from' => 0,
                    'to' => 0,
                    'prev_page_url' => null,
                    'next_page_url' => null
                ]
            ];
        }
    }

    /**
     * Get approval counts for a user.
     */
    public function getApprovalCounts(int $employmentId, array $filters = []): array
    {
        try {
            $year = $filters['year'] ?? null;

            Log::info('getApprovalCounts called', [
                'employment_id' => $employmentId,
                'filters' => $filters
            ]);

            // Base query
            $baseQuery = ApprovalRequestDetail::where('employment_id', $employmentId)
                ->whereHas('request.module', fn($q) => $q->where('table_name', 'transactions'));

            // Get pending count
            $pendingQuery = clone $baseQuery;
            $pendingQuery->where('status', 'pending')
                ->whereHas('request', fn($q) => $q->where('status', 'pending'));
            
            if ($year && $year !== 'all') {
                $pendingQuery->whereHas('request.transaction', fn($q) => $q->whereYear('transaction_date', $year));
            }
            $pendingCount = $pendingQuery->count();

            // Get approved count
            $approvedQuery = clone $baseQuery;
            $approvedQuery->where('status', 'approved');
            
            if ($year && $year !== 'all') {
                $approvedQuery->whereHas('request.transaction', fn($q) => $q->whereYear('transaction_date', $year));
            }
            $approvedCount = $approvedQuery->count();

            // Get rejected count
            $rejectedQuery = clone $baseQuery;
            $rejectedQuery->where('status', 'rejected');
            
            if ($year && $year !== 'all') {
                $rejectedQuery->whereHas('request.transaction', fn($q) => $q->whereYear('transaction_date', $year));
            }
            $rejectedCount = $rejectedQuery->count();

            return [
                'success' => true,
                'data' => [
                    'pending' => $pendingCount,
                    'approved' => $approvedCount,
                    'rejected' => $rejectedCount
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error in getApprovalCounts: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error fetching approval counts',
                'data' => [
                    'pending' => 0,
                    'approved' => 0,
                    'rejected' => 0
                ]
            ];
        }
    }

    /**     * Cancel an approval request.
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

            // Update transaction status - cancelled
            $transaction = Transaction::find($transactionId);
            if ($transaction) {
                $transaction->update([
                    'status' => Transaction::STATUS_CANCELLED,
                    'status_approval' => Transaction::APPROVAL_STATUS_CANCELLED,
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
                'reference_number' => $request->reference_number,
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
                        'reference_number' => $request->reference_number,
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