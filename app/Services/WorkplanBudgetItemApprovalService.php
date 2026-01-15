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
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkplanBudgetItemApprovalService
{
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

            // Find active template for this module
            $template = ApprovalFlowTemplate::where('module_id', $module->id)
                ->where('is_active', true)
                ->orderBy('priority')
                ->first();

            if (! $template) {
                return [
                    'success' => false,
                    'message' => 'Approval template belum dikonfigurasi untuk module ini.',
                ];
            }

            // Get current user's employment
            $employee = Auth::user();
            $requesterEmployment = $employee ? $employee->employment : null;
            $requesterId = $requesterEmployment ? $requesterEmployment->id : null;

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

            Log::info('Built approval chain', [
                'item_id' => $itemId,
                'template_id' => $template->id,
                'use_uppline_chain' => $template->use_uppline_chain,
                'use_threshold' => $template->use_threshold,
                'division_id' => $divisionId,
                'chain_count' => count($approvalChain),
                'chain' => $approvalChain,
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
            foreach ($approvalChain as $index => $approver) {
                ApprovalRequestDetail::create([
                    'request_id' => $request->id,
                    'phase' => $approver['phase'],
                    'level_sequence' => $index + 1, // Sequential from 1
                    'employment_id' => $approver['employment_id'],
                    'employment_name' => $approver['employment_name'],
                    'status' => 'pending',
                ]);
            }

            // Update item status
            $item->update(['status' => 'pending']);

            DB::commit();

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
            Log::error('Submit for approval failed: '.$e->getMessage(), [
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

        // Phase 1: Uppline Chain (if enabled)
        if ($template->use_uppline_chain) {
            $upplineApprovers = $this->resolveUplineApprovers($template->id, $requesterEmployment, $divisionId);
            foreach ($upplineApprovers as $approver) {
                $chain[] = array_merge($approver, ['phase' => 'uppline']);
            }
        }

        // Phase 2: Master Flow Details
        $masterFlowApprovers = $this->getMasterFlowApprovers($template, $amount);
        foreach ($masterFlowApprovers as $approver) {
            $chain[] = array_merge($approver, ['phase' => 'master_flow']);
        }

        return $chain;
    }

    /**
     * Resolve uppline chain approvers based on ApprovalFlowUpplineConfigs.
     * 
     * Logic:
     * 1. Get uppline config for specific division first, if not found use default (division_id = NULL)
     * 2. Build recursive uppline chain from requester
     * 3. Filter chain by job_level_name defined in config
     * 4. Skip if uppline in chain is missing, but keep sequence sequential
     * 
     * @param int $templateId
     * @param Employment $requesterEmployment
     * @param int|null $divisionId
     * @return array
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
     * @param ApprovalFlowTemplate $template
     * @param mixed $amount
     * @return array
     */
    protected function getMasterFlowApprovers(ApprovalFlowTemplate $template, mixed $amount): array
    {
        $query = ApprovalFlowDetail::with('employment.employee')
            ->where('template_id', $template->id)
            ->where('is_required', true);

        // Apply threshold filter if enabled
        if ($template->use_threshold) {
            $query->where(function ($q) use ($amount) {
                $q->whereNull('threshold_amount')
                    ->orWhere('threshold_amount', '>=', $amount);
            });
        }

        $flowDetails = $query->orderBy('level_sequence')->get();

        return $flowDetails->map(function ($detail) {
            return [
                'employment_id' => $detail->employment_id,
                'employment_name' => $detail->employment?->employee?->name ?? 'Unknown',
                'threshold_amount' => $detail->threshold_amount,
            ];
        })->toArray();
    }

    /**
     * Process an approval action (approve/reject).
     *
     * @param  string  $action  ('approve' or 'reject')
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
            'employment.employee',
        ])
            ->where('employment_id', $employmentId)
            ->where('status', 'pending')
            ->whereHas('request', fn ($q) => $q->where('status', 'pending'))
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
                $item = WorkplanBudgetItem::with('workplan', 'category')
                    ->find($detail->request->reference_id);

                return [
                    'detail_id' => $detail->id,
                    'request_id' => $detail->request_id,
                    'reference_number' => $detail->request->reference_number,
                    'level' => $detail->level_sequence,
                    'total_levels' => $detail->request->total_levels,
                    'requested_at' => $detail->request->requested_at?->format('Y-m-d H:i:s'),
                    'item' => $item ? [
                        'id' => $item->id,
                        'description' => $item->description,
                        'total' => $item->total,
                        'category' => $item->category?->name,
                        'workplan' => $item->workplan?->name,
                    ] : null,
                ];
            });

        return [
            'success' => true,
            'data' => $pendingDetails->values()->toArray(),
            'count' => $pendingDetails->count(),
        ];
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
            $item = WorkplanBudgetItem::find($itemId);
            if ($item) {
                $item->update(['status' => 'draft']);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Approval request berhasil dibatalkan.',
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
