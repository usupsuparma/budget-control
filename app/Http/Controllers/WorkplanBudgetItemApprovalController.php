<?php

namespace App\Http\Controllers;

use App\Services\WorkplanBudgetItemApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WorkplanBudgetItemApprovalController extends Controller
{
    protected WorkplanBudgetItemApprovalService $approvalService;

    public function __construct(WorkplanBudgetItemApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Submit a workplan budget item for approval.
     */
    public function submitForApproval(int $id)
    {
        $result = $this->approvalService->submitForApproval($id);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Approve an approval request detail.
     */
    public function approve(Request $request, int $detailId)
    {
        try {
            $request->validate([
                'comments' => 'nullable|string|max:1000',
            ]);

            $employmentId = $this->getEmploymentIdForCurrentUser();

            if (! $employmentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki employment yang valid.',
                ], 403);
            }

            Log::info('WorkplanBudgetItemApprovalController.approve', [
                'detailId' => $detailId,
                'employmentId' => $employmentId,
                'comments' => $request->input('comments'),
            ]);

            $result = $this->approvalService->processApproval(
                $detailId,
                'approve',
                $employmentId,
                $request->input('comments')
            );

            Log::info('Approval berhasil', [
                'WorkplanBudgetItemApprovalController' => $result,
            ]);

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Throwable $th) {
            Log::error('WorkplanBudgetItemApprovalController.approve', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses approval.',
            ], 500);
        }
    }

    /**
     * Reject an approval request detail.
     */
    public function reject(Request $request, int $detailId)
    {
        $request->validate([
            'comments' => 'required|string|max:1000',
        ]);

        $employmentId = $this->getEmploymentIdForCurrentUser();

        if (! $employmentId) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak memiliki employment yang valid.',
            ], 403);
        }

        $result = $this->approvalService->processApproval(
            $detailId,
            'reject',
            $employmentId,
            $request->input('comments')
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get approval status for an item.
     */
    public function getApprovalStatus(int $id)
    {
        $result = $this->approvalService->getApprovalStatus($id);

        return response()->json($result, $result['success'] ? 200 : 404);
    }

    /**
     * Get pending approvals for current user.
     */
    public function myPendingApprovals()
    {
        $employmentId = $this->getEmploymentIdForCurrentUser();

        if (! $employmentId) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak memiliki employment yang valid.',
            ], 403);
        }

        $result = $this->approvalService->getPendingApprovalsForUser($employmentId);

        return response()->json($result);
    }

    /**
     * Cancel an approval request.
     */
    public function cancel(int $id)
    {
        $result = $this->approvalService->cancelApproval($id);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get employment ID for current authenticated user.
     * Note: Auth::user() returns Employee model (see config/auth.php)
     */
    protected function getEmploymentIdForCurrentUser(): ?int
    {
        $employee = Auth::user();

        if (! $employee) {
            return null;
        }

        // Auth::user() returns Employee, which has employment relationship
        if ($employee->employment) {
            return $employee->employment->id;
        }

        return null;
    }
}
