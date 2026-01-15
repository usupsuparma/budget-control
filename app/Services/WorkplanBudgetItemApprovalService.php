<?php

namespace App\Services;

use App\Models\ApprovalFlowDetail;
use App\Models\ApprovalFlowTemplate;
use App\Models\ApprovalModule;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestDetail;
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
            $item = WorkplanBudgetItem::findOrFail($itemId);

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

            // Find template with threshold
            $template = ApprovalFlowTemplate::where('module_id', $module->id)
                ->where('is_active', true)
                ->where('use_threshold', true)
                ->where('condition_field', 'total')
                ->orderBy('priority')
                ->first();

            if (! $template) {
                return [
                    'success' => false,
                    'message' => 'Approval template dengan threshold untuk field "total" belum dikonfigurasi.',
                ];
            }

            // Get applicable flow details based on item's total
            $flowDetails = $this->getApplicableFlowDetails($template->id, $item->total);

            if ($flowDetails->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada approver yang sesuai untuk nominal ini.',
                ];
            }

            DB::beginTransaction();

            // Get current user's employment_id
            // Note: Auth::user() returns Employee model (see config/auth.php)
            $employee = Auth::user();
            Log::info($employee);
            $requesterId = null;
            if ($employee && $employee->employment) {
                $requesterId = $employee->employment->id;
            }

            // Create approval request
            $request = ApprovalRequest::create([
                'module_id' => $module->id,
                'reference_id' => $itemId,
                'reference_number' => $this->generateReferenceNumber($item),
                'template_id' => $template->id,
                'template_snapshot' => json_encode($flowDetails->toArray()),
                'status' => 'pending',
                'current_phase' => 1,
                'current_level' => 1,
                'total_levels' => $flowDetails->count(),
                'requester_id' => $requesterId,
                'requested_at' => now(),
            ]);

            // Create approval request details for each approver
            foreach ($flowDetails as $detail) {
                $employeeName = $detail->employment?->employee
                    ? $detail->employment->employee->name
                    : 'Unknown';

                ApprovalRequestDetail::create([
                    'request_id' => $request->id,
                    'phase' => 1,
                    'level_sequence' => $detail->level_sequence,
                    'employment_id' => $detail->employment_id,
                    'employment_name' => $employeeName,
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
                    'total_approvers' => $flowDetails->count(),
                ],
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Submit for approval failed: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengajukan approval: '.$e->getMessage(),
            ];
        }
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
