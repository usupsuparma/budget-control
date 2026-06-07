<?php

namespace App\Services;

use App\Models\ApprovalFlowDetail;
use App\Models\ApprovalFlowTemplate;
use App\Models\ApprovalFlowUpplineConfigs;
use App\Models\ApprovalModule;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestDetail;
use App\Models\Employment;
use App\Models\WorkplanBudgetItem;
use App\Services\BudgetLedgerService\BudgetLedgerService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkplanBudgetItemApprovalService
{
    private const APPROVAL_CATEGORY = 'approval';
    private const APPROVAL_TASK_TITLE = 'Permintaan Approval Workplan Budget';
    private const APPROVAL_TASK_REFERENCE_TYPE = 'workplan_budget_item_approval';

    protected BudgetLedgerService $budgetLedgerService;
    protected NotificationService $notificationService;

    public function __construct(BudgetLedgerService $budgetLedgerService, NotificationService $notificationService)
    {
        $this->budgetLedgerService = $budgetLedgerService;
        $this->notificationService = $notificationService;
    }

    /**
     * Submit a workplan budget item for approval.
     */
    public function submitForApproval(int $itemId): array
    {
        try {
            $item = WorkplanBudgetItem::with('workplan')->findOrFail($itemId);

            // Check if already has pending approval
            $existingRequest = ApprovalRequest::where('reference_id', $itemId)
                ->whereHas('module', fn ($q) => $q->where('table_name', 'workplan_budget_items'))
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                return [
                    'success' => false,
                    'message' => 'Item sudah dalam proses approval.',
                ];
            }

            // Find module for workplan_budget_items
            $module = ApprovalModule::where('table_name', 'workplan_budget_items')
                ->where('is_active', true)
                ->first();

            if (! $module) {
                return [
                    'success' => false,
                    'message' => 'Approval module untuk workplan_budget_items belum dikonfigurasi.',
                ];
            }

            Log::info('WorkplanBudgetItemApprovalService: Module found', [
                'module_id' => $module->id,
                'module_name' => $module->module_name,
            ]);

            // Find active template for this module
            $template = ApprovalFlowTemplate::where('module_id', $module->id)
                ->where('is_active', true)
                ->orderBy('priority')
                ->first();
            Log::info('WorkplanBudgetItemApprovalService: Template found', [
                'template_id' => $template ? $template->id : null,
                'template_name' => $template ? $template->template_name : null,
            ]);

            if (! $template) {
                return [
                    'success' => false,
                    'message' => 'Approval template belum dikonfigurasi untuk module ini.',
                ];
            }

            // Get current user's employment
            $employee = Auth::user();
            $requesterEmployment = $employee ? $employee->employment : null;
            Log::info('WorkplanBudgetItemApprovalService: Requester employment', [
                'employee_id' => $employee ? $employee->id : null,
                'employment_id' => $requesterEmployment ? $requesterEmployment->id : null,
            ]);
            $requesterId = $requesterEmployment ? $requesterEmployment->id : null;
            Log::info('Submitting WorkplanBudgetItem for approval', [
                'item_id' => $itemId,
                'item_description' => $item->description,
                'item_total' => $item->total,
                'requester_employment_id' => $requesterId,
            ]);

            if (! $requesterEmployment) {
                return [
                    'success' => false,
                    'message' => 'Data employment Anda tidak ditemukan.',
                ];
            }

            // Get division from the workplan budget item
            $divisionId = $item->getDivisionId();

            // Build approval chain based on template configuration
            $approvalChain = $this->buildApprovalChain($template, $requesterEmployment, $divisionId, $item->total);

            if (empty($approvalChain)) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada approver yang sesuai untuk request ini.',
                ];
            }

            Log::info('Final approval chain ready for submission', [
                'item_id' => $itemId,
                'template_id' => $template->id,
                'template_name' => $template->template_name,
                'use_uppline_chain' => $template->use_uppline_chain,
                'use_threshold' => $template->use_threshold,
                'division_id' => $divisionId,
                'amount' => $item->total,
                'chain_count' => count($approvalChain),
                'chain_details' => $approvalChain,
            ]);

            DB::beginTransaction();

            // Create approval request
            $request = ApprovalRequest::create([
                'module_id' => $module->id,
                'reference_id' => $itemId,
                'reference_number' => $this->generateReferenceNumber($item),
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
                    'level_sequence' => $approver['level_sequence'], // Use pre-calculated sequence
                    'employment_id' => $approver['employment_id'],
                    'employment_name' => $approver['employment_name'],
                    'status' => 'pending',
                ]);
            }

            // Update item status
            $item->update(['status' => 'pending']);

            DB::commit();

            // Notify first approver
            $firstApprover = $approvalChain[0];
            $this->notificationService->sendToEmployment(
                $firstApprover['employment_id'],
                'approval',
                'Permintaan Approval Workplan Budget',
                "Ada permintaan approval baru untuk Workplan Budget Item: {$item->description} senilai " . number_format($item->total, 0, ',', '.'),
                'workplan_budget_item_approval',
                $itemId
            );

            return [
                'success' => true,
                'message' => 'Item berhasil diajukan untuk approval.',
                'data' => [
                    'request_id' => $request->id,
                    'total_approvers' => count($approvalChain),
                ],
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('WorkplanBudgetItemApprovalService.submitForApproval', [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengajukan approval: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Build approval chain based on template configuration.
     * 
     * 1. If use_uppline_chain=true: resolve uppline chain first
     * 2. Then add master flow details (threshold-based or all-levels)
     * 3. Ensure level_sequence is sequential across both phases
     * 
     * @param ApprovalFlowTemplate $template
     * @param Employment $requesterEmployment
     * @param int|null $divisionId
     * @param mixed $amount
     * @return array
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
     * 
     * Logic:
     * 1. Get uppline config for specific division first, if not found use default (division_id = NULL)
     * 2. Build recursive uppline chain from requester
     * 3. Filter chain by job_level_name defined in config
     * 4. If use_threshold is enabled, skip approvers where amount < threshold_amount
     * 5. Skip if uppline in chain is missing, but keep sequence sequential
     * 
     * @param ApprovalFlowTemplate $template
     * @param Employment $requesterEmployment
     * @param int|null $divisionId
     * @param mixed $amount
     * @return array
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
                return strtolower($uppline['job_level_name']) === strtolower($jobLevelName);
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
                // Skip this level if no matching uppline found (as per requirement)
            }
        }

        return $approvers;
    }

    /**
     * Get uppline configuration for template.
     * Priority: Specific division first, then default (division_id = NULL)
     * 
     * @param int $templateId
     * @param int|null $divisionId
     * @return \Illuminate\Support\Collection
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
     * 
     * @param Employment $employment
     * @return array
     */
    protected function buildRecursiveUpplineChain(Employment $employment): array
    {
        $chain = [];
        $currentEmployment = $employment;
        $visitedIds = [$employment->id]; // Prevent infinite loop

        while ($currentEmployment && $currentEmployment->uppline_id) {
            // Get uppline's employee
            $upplineEmployee = \App\Models\Employee::find($currentEmployment->uppline_id);

            if (! $upplineEmployee || ! $upplineEmployee->employment) {
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
                'employment_name' => $upplineEmployee->name,
                'job_level_name' => $upplineEmployment->job_level_name,
                'job_level_id' => $upplineEmployment->job_level_id,
            ];

            $currentEmployment = $upplineEmployment;
        }

        return $chain;
    }

    /**
     * Get master flow approvers from ApprovalFlowDetails.
     * 
     * Logic untuk threshold-based approval:
     * - Ambil semua level dengan threshold < amount (mereka harus approve karena perlu escalate)
     * - Ambil level pertama dengan threshold >= amount (ini final level yang cukup)
     * 
     * Contoh: Amount 120jt
     * - Threshold 10jt: INCLUDE (120jt > 10jt, perlu approve)
     * - Threshold 100jt: INCLUDE (120jt > 100jt, perlu approve)  
     * - Threshold 200jt: INCLUDE (120jt <= 200jt, final level)
     * - Threshold 1M: SKIP (tidak perlu level setinggi ini)
     * 
     * @param ApprovalFlowTemplate $template
     * @param mixed $amount
     * @return array
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
                    'employee_name' => $d->employment->employee->name ?? 'N/A',
                ];
            }),
        ]);

        return $flowDetails->map(function ($detail) {
            $employee = $detail->employment?->employee;
            $employmentName = $employee ? $employee->name : 'Unknown';
            
            return [
                'employment_id' => $detail->employment_id,
                'employment_name' => $employmentName,
                'threshold_amount' => $detail->threshold_amount,
                'job_level_name' => $detail->employment?->job_level_name,
            ];
        })->toArray();
    }

    /**
     * Process multiple approval actions (approve/reject).
     */
    public function bulkProcessApproval(array $detailIds, string $action, int $approverId, ?string $comments = null): array
    {
        try {
            DB::beginTransaction();
            $results = [];
            $successCount = 0;
            $failCount = 0;

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
                'message' => "Proses bulk $action selesai. Berhasil: $successCount, Gagal: $failCount.",
                'data' => [
                    'results' => $results,
                    'success_count' => $successCount,
                    'fail_count' => $failCount,
                ],
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Bulk process approval failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memproses bulk approval: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process an approval action (approve/reject).
     *
     * @param  int  $requestDetailId
     * @param  string  $action  ('approve' or 'reject')
     * @param  int  $approverId
     * @param  string|null  $comments
     * @return array
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
            Log::error('Process approval failed: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memproses approval: '.$e->getMessage(),
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
            // All approved - update request and item
            $request->update([
                'status' => 'approved',
                'current_level' => $request->total_levels,
                'completed_at' => now(),
            ]);

            // Update the actual item
            $item = WorkplanBudgetItem::find($request->reference_id);
            if ($item) {
                $item->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'approval_notes' => $comments,
                ]);

                // Record initial budget mutation (Saldo Awal)
                $mutationResult = $this->budgetLedgerService->recordInitialBudgetMutation($item->id);
                
                if (!$mutationResult['success']) {
                    Log::warning('Failed to record initial budget mutation', [
                        'item_id' => $item->id,
                        'error' => $mutationResult['message'],
                    ]);
                }

                // Notify requester that item is fully approved
                $this->notificationService->sendToEmployment(
                    $request->requester_id,
                    'approval',
                    'Workplan Budget Disetujui',
                    "Workplan Budget Item Anda: {$item->description} telah disetujui sepenuhnya.",
                    'workplan_budget_item_approval',
                    $item->id
                );
            }

            return [
                'success' => true,
                'message' => 'Item telah disetujui sepenuhnya.',
                'is_fully_approved' => true,
            ];
        } else {
            // Update current level
            $request->update([
                'current_level' => $detail->level_sequence + 1,
                'status' => 'pending',
            ]);

            // Notify next approver
            $nextApprover = ApprovalRequestDetail::where('request_id', $request->id)
                ->where('status', 'pending')
                ->orderBy('level_sequence')
                ->first();
            
            if ($nextApprover) {
                $item = WorkplanBudgetItem::find($request->reference_id);
                $this->notificationService->sendToEmployment(
                    $nextApprover->employment_id,
                    'approval',
                    'Permintaan Approval Workplan Budget',
                    "Ada permintaan approval baru untuk Workplan Budget Item: " . ($item->description ?? 'N/A'),
                    'workplan_budget_item_approval',
                    $request->reference_id
                );
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

        // Update the actual item
        $item = WorkplanBudgetItem::find($request->reference_id);
        if ($item) {
            $item->update([
                'status' => 'rejected',
                'approval_notes' => $comments,
            ]);

            // Notify requester that item is rejected
            $this->notificationService->sendToEmployment(
                $request->requester_id,
                'approval',
                'Workplan Budget Ditolak',
                "Workplan Budget Item Anda: {$item->description} telah ditolak oleh {$detail->employment_name}.",
                'workplan_budget_item_approval',
                $item->id
            );
        }

        return [
            'success' => true,
            'message' => 'Item telah ditolak.',
            'rejected_by' => $detail->employment_name,
        ];
    }

    /**
     * Get approval status for an item.
     */
    public function getApprovalStatus(int $itemId): array
    {
        $item = WorkplanBudgetItem::with('approvalRequest.details.employment.employee')
            ->find($itemId);

        if (! $item) {
            return [
                'success' => false,
                'message' => 'Item tidak ditemukan.',
            ];
        }

        $request = $item->approvalRequest;

        if (! $request) {
            return [
                'success' => true,
                'data' => [
                    'item_id' => $itemId,
                    'item_status' => $item->status,
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
                'status' => $detail->status,
                'approved_at' => $detail->approved_at?->format('Y-m-d H:i:s'),
            ];
        });

        return [
            'success' => true,
            'data' => [
                'item_id' => $itemId,
                'item_status' => $item->status,
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
        $pendingDetails = ApprovalRequestDetail::with([
            'request.module',
            'request.details',
            'employment.employee',
        ])
            ->where('employment_id', $employmentId)
            ->where('status', 'pending')
            ->whereHas('request', function ($query) {
                $query->where('status', 'pending')
                    ->whereHas('module', fn ($moduleQuery) => $moduleQuery->where('table_name', 'workplan_budget_items'));
            })
            ->get()
            ->filter(function ($detail) {
                // Only return if this is the next in sequence
                $nextPending = ApprovalRequestDetail::where('request_id', $detail->request_id)
                    ->where('status', 'pending')
                    ->orderBy('level_sequence')
                    ->first();

                return $nextPending && $nextPending->id === $detail->id;
            })
            ->map(function ($detail) {
                $item = WorkplanBudgetItem::with([
                    'workplan.KPIDepartment.department',
                    'workplan.KPIDepartment.kpiDivision.division',
                    'workplan.kpiSection.section.department',
                    'category',
                    'approvalRequest.details',
                ])->find($detail->request->reference_id);

                // Calculate total qty & budget
                $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
                $totalQty = 0;
                $monthlyData = [];
                if ($item) {
                    foreach ($months as $m) {
                        $val = (int) ($item->{"activity_$m"} ?? 0);
                        $totalQty += $val;
                        $monthlyData[$m] = $val;
                    }
                }

                $isVerified = $item && $item->verification_status === 'verified' && $item->price_final && (float) $item->price_final > 0;
                $unitPrice = $isVerified ? (float) $item->price_final : (float) ($item->price_estimation ?? 0);
                $totalBudget = $unitPrice * $totalQty;

                // Resolve division name from workplan
                $divisionName = null;
                $departmentName = null;
                if ($item && $item->workplan) {
                    if ($item->workplan->kpi_type === 'department' && $item->workplan->KPIDepartment) {
                        $departmentName = $item->workplan->KPIDepartment->department?->name;
                        $divisionName = $item->workplan->KPIDepartment->kpiDivision?->division?->name;
                    } elseif ($item->workplan->kpi_type === 'section' && $item->workplan->kpiSection) {
                        $departmentName = $item->workplan->kpiSection->section?->department?->name;
                        $divisionName = $item->workplan->kpiSection->section?->department?->division?->name ?? null;
                    }
                }

                // Build approval timeline details
                $timelineDetails = [];
                if ($detail->request && $detail->request->details) {
                    $timelineDetails = $detail->request->details
                        ->sortBy('level_sequence')
                        ->map(fn ($d) => [
                            'id' => $d->id,
                            'level_sequence' => $d->level_sequence,
                            'employment_name' => $d->employment_name,
                            'status' => $d->status,
                            'approved_at' => $d->approved_at?->format('Y-m-d H:i:s'),
                        ])->values()->toArray();
                }

                return [
                    'detail_id' => $detail->id,
                    'request_id' => $detail->request_id,
                    'reference_number' => $detail->request->reference_number,
                    'level' => $detail->level_sequence,
                    'total_levels' => $detail->request->total_levels,
                    'requested_at' => $detail->request->requested_at?->format('Y-m-d H:i:s'),
                    'requester_name' => Employment::with('employee')->find($detail->request->requester_id)?->employee?->name ?? '-',
                    'timeline' => $timelineDetails,
                    'item' => $item ? [
                        'id' => $item->id,
                        'description' => $item->description,
                        'category_type' => $item->category_type,
                        'category_name' => $item->category?->name,
                        'stock_code' => $item->stock_code,
                        'budget_code' => $item->budget_code,
                        'cost_center' => $item->cost_center,
                        'supplier_name' => $item->supplier_name,
                        'unit_name' => $item->unit_name,
                        'cons_rate' => $item->cons_rate,
                        'price_estimation' => $item->price_estimation,
                        'price_final' => $item->price_final,
                        'verification_status' => $item->verification_status,
                        'total' => $item->total,
                        'total_qty' => $totalQty,
                        'unit_price' => $unitPrice,
                        'total_budget' => $totalBudget,
                        'monthly' => $monthlyData,
                        'workplan_activity' => $item->workplan?->activity,
                        'workplan_year' => $item->workplan?->year,
                        'division_name' => $divisionName,
                        'department_name' => $departmentName,
                    ] : null,
                ];
            })
            ->filter(fn ($approval) => $approval['item'] !== null)
            ->sortBy('requested_at')
            ->values();

        $this->deleteStaleApprovalTaskNotifications($employmentId, $pendingDetails->pluck('item.id')->all());

        return [
            'success' => true,
            'data' => $pendingDetails->toArray(),
            'count' => $pendingDetails->count(),
        ];
    }

    protected function deleteStaleApprovalTaskNotifications(int $employmentId, array $activeItemIds): void
    {
        $employeeId = Employment::whereKey($employmentId)->value('employee_id');

        if (! $employeeId) {
            return;
        }

        $this->notificationService->deleteTaskNotificationsExceptReferences(
            self::APPROVAL_CATEGORY,
            self::APPROVAL_TASK_TITLE,
            self::APPROVAL_TASK_REFERENCE_TYPE,
            $activeItemIds,
            [(int) $employeeId]
        );
    }

    /**
     * Cancel an approval request.
     */
    public function cancelApproval(int $itemId): array
    {
        try {
            $request = ApprovalRequest::where('reference_id', $itemId)
                ->whereHas('module', fn ($q) => $q->where('table_name', 'workplan_budget_items'))
                ->where('status', 'pending')
                ->first();

            if (! $request) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada approval request yang aktif untuk item ini.',
                ];
            }

            DB::beginTransaction();

            $item = WorkplanBudgetItem::find($itemId);
            $pendingDetails = ApprovalRequestDetail::with('employment')
                ->where('request_id', $request->id)
                ->where('status', 'pending')
                ->get();
            $pendingEmployeeIds = $pendingDetails
                ->pluck('employment.employee_id')
                ->filter()
                ->values()
                ->all();

            $deletedReferencedNotifications = $this->notificationService->deleteByReference(
                'approval',
                'workplan_budget_item_approval',
                $itemId,
                $pendingEmployeeIds
            );

            $deletedLegacyNotifications = 0;
            if ($item) {
                $deletedLegacyNotifications = $this->notificationService->deleteMatching(
                    'approval',
                    'Permintaan Approval Workplan Budget',
                    [
                        "Ada permintaan approval baru untuk Workplan Budget Item: {$item->description} senilai " . number_format($item->total, 0, ',', '.'),
                        "Ada permintaan approval baru untuk Workplan Budget Item: {$item->description}",
                    ],
                    $pendingEmployeeIds
                );
            }

            // Update request status
            $request->update([
                'status' => 'cancelled',
                'completed_at' => now(),
            ]);

            // Mark all pending details as cancelled
            ApprovalRequestDetail::where('request_id', $request->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            // Update item status back to draft
            if ($item) {
                $item->update(['status' => 'draft']);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Approval request berhasil dibatalkan.',
                'data' => [
                    'deleted_notifications' => $deletedReferencedNotifications + $deletedLegacyNotifications,
                ],
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Cancel approval failed: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membatalkan approval: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get flow details applicable for the given amount.
     */
    protected function getApplicableFlowDetails(int $templateId, mixed $amount)
    {
        return ApprovalFlowDetail::with('employment.employee')
            ->where('template_id', $templateId)
            ->where(function ($query) use ($amount) {
                $query->whereNull('threshold_amount')
                    ->orWhere('threshold_amount', '<=', $amount);
            })
            ->where('is_required', true)
            ->orderBy('level_sequence')
            ->get();
    }

    /**
     * Generate reference number for approval request.
     */
    protected function generateReferenceNumber(WorkplanBudgetItem $item): string
    {
        $prefix = 'WBI-APR';
        $date = now()->format('Ymd');
        $sequence = ApprovalRequest::whereDate('created_at', now())->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }
}
