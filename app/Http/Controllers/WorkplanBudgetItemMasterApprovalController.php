<?php

namespace App\Http\Controllers;

use App\Services\WorkplanBudgetItemApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WorkplanBudgetItemMasterApprovalController extends Controller
{
    protected WorkplanBudgetItemApprovalService $approvalService;

    public function __construct(WorkplanBudgetItemApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Submit a workplan budget item for approval.
     */
    public function submitForApproval(Request $request, $id)
    {
        try {
            $result = $this->approvalService->submitForApproval((int) $id);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('WBI submitForApproval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit for approval: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve an approval request detail.
     */
    public function approve(Request $request, $detailId)
    {
        try {
            $employee = Auth::user();
            $employmentId = $employee?->employment?->id;

            if (!$employmentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data employment Anda tidak ditemukan.',
                ]);
            }

            $comments = $request->input('comments');
            $result = $this->approvalService->processApproval((int) $detailId, 'approve', $employmentId, $comments);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('WBI approve error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject an approval request detail.
     */
    public function reject(Request $request, $detailId)
    {
        try {
            $employee = Auth::user();
            $employmentId = $employee?->employment?->id;

            if (!$employmentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data employment Anda tidak ditemukan.',
                ]);
            }

            $comments = $request->input('comments');

            if (empty($comments)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alasan penolakan wajib diisi.',
                ]);
            }

            $result = $this->approvalService->processApproval((int) $detailId, 'reject', $employmentId, $comments);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('WBI reject error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk process approval requests (approve/reject).
     */
    public function bulkProcess(Request $request)
    {
        try {
            $request->validate([
                'detail_ids' => 'required|array',
                'detail_ids.*' => 'exists:approval_request_details,id',
                'action' => 'required|in:approve,reject',
                'comments' => 'nullable|string|max:1000',
            ]);

            $employee = Auth::user();
            $employmentId = $employee?->employment?->id;

            if (!$employmentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data employment Anda tidak ditemukan.',
                ]);
            }

            $action = $request->input('action');
            $comments = $request->input('comments');

            if ($action === 'reject' && empty($comments)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alasan penolakan wajib diisi untuk bulk reject.',
                ]);
            }

            $result = $this->approvalService->bulkProcessApproval(
                $request->input('detail_ids'),
                $action,
                $employmentId,
                $comments
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('WBI bulkProcess error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses bulk approval: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get approval status for an item.
     */
    public function getApprovalStatus(Request $request, $id)
    {
        try {
            $result = $this->approvalService->getApprovalStatus((int) $id);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('WBI getApprovalStatus error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get approval status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending approvals for current user.
     */
    public function myPendingApprovals(Request $request)
    {
        try {
            $employee = Auth::user();
            $employmentId = $employee?->employment?->id;

            if (!$employmentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data employment Anda tidak ditemukan.',
                ]);
            }

            $result = $this->approvalService->getPendingApprovalsForUser($employmentId);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('WBI myPendingApprovals error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get pending approvals: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel an approval request.
     */
    public function cancel(Request $request, $id)
    {
        try {
            $result = $this->approvalService->cancelApproval((int) $id);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('WBI cancel error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel approval: ' . $e->getMessage(),
            ], 500);
        }
    }
}
