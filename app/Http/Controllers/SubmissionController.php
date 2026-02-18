<?php

namespace App\Http\Controllers;

use App\Services\ApprovalTransactionService\ApprovalTransactionService;
use App\Services\LogService\LogService;
use App\Services\LpjService\LpjService;
use App\Services\SubmissionService\SubmissionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubmissionController extends Controller
{
    public function __construct(
        private readonly SubmissionService $submissionService,
        private readonly ApprovalTransactionService $approvalTransactionService,
        private readonly LpjService $lpjService,
        private readonly LogService $logService,
    ) {}

    /* ========================
        VIEW METHODS
    ======================== */

    public function user()
    {
        $title = 'Submission Users';
        $data = $this->submissionService->getUserPageData();

        return view('pages.submission.user', array_merge(compact('title'), $data));
    }

    public function user_create()
    {
        $title = 'Submission Users Create';

        return view('pages.submission.user_create', compact('title'));
    }

    public function approval()
    {
        $title = 'Approval Submission';
        $data = $this->submissionService->getApprovalPageData();

        return view('pages.submission.approval', array_merge(compact('title'), $data));
    }

    /* ========================
        DATA / SUMMARY
    ======================== */

    public function getSummary(Request $request)
    {
        try {
            $result = $this->submissionService->getSummary([
                'year' => $request->input('year'),
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error fetching summary: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching summary: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getData(Request $request)
    {
        try {
            $result = $this->submissionService->getTransactions([
                'year' => $request->input('year'),
                'status' => $request->input('status'),
                'per_page' => $request->input('per_page', 10),
            ]);

            return response()->json($result);
        } catch (\Throwable $th) {
            Log::error('Error fetching transactions: '.$th->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching transactions: '.$th->getMessage(),
            ], 500);
        }
    }

    /* ========================
        CRUD ACTIONS
    ======================== */

    public function store(Request $request)
    {
        Log::info('Store transaction request data:', $request->all());

        $validator = Validator::make($request->all(), [
            'transaction_date' => 'required|date',
            'planned_usage_date' => 'nullable|date|after_or_equal:today',
            'job_level_id' => 'required',
            'job_position_id' => 'required',
            'program_id' => 'required',
            'purpose' => 'required|string',
            'urgency' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.goods_service_name' => 'required|string',
            'items.*.budget_id' => 'required',
            'items.*.unit_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            Log::error('Validation error in create transaction: ', $validator->errors()->toArray());

            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->submissionService->createTransaction($request->all());

            $statusCode = $result['status_code'] ?? ($result['success'] ? 200 : 422);

            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            Log::error('Error creating transaction: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error creating transaction: '.$e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $result = $this->submissionService->getTransactionDetail((int) $id);

            $statusCode = $result['status_code'] ?? ($result['success'] ? 200 : 404);

            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            Log::error('Error fetching transaction: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'transaction_date' => 'required|date',
            'planned_usage_date' => 'nullable|date|after_or_equal:today',
            'job_level_id' => 'required',
            'job_position_id' => 'required',
            'program_id' => 'required',
            'purpose' => 'required|string',
            'urgency' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.goods_service_name' => 'required|string',
            'items.*.budget_id' => 'required',
            'items.*.unit_id' => 'required',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            Log::error('Validation error in update transaction: ', $validator->errors()->toArray());

            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->submissionService->updateTransaction((int) $id, $request->all());

            $statusCode = $result['status_code'] ?? ($result['success'] ? 200 : 422);

            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            Log::error('Error updating transaction: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error updating transaction: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $result = $this->submissionService->deleteTransaction((int) $id);

            $statusCode = $result['status_code'] ?? ($result['success'] ? 200 : 403);

            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            Log::error('Error deleting transaction: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error deleting transaction: '.$e->getMessage(),
            ], 500);
        }
    }

    /* ========================
        BUDGET / DROPDOWN
    ======================== */

    public function getBudgetInfo($budgetId)
    {
        try {
            $result = $this->submissionService->getBudgetInfo((int) $budgetId);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Budget not found',
            ], 404);
        }
    }

    /**
     * Get job positions filtered by job level
     */
    public function getJobPositions($jobLevelId)
    {
        try {
            $result = $this->submissionService->getJobPositionsByLevel((int) $jobLevelId);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching job positions: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get programs (KPI Workplans) based on job level
     */
    public function getPrograms($jobLevelId)
    {
        try {
            $result = $this->submissionService->getProgramsByJobLevel((int) $jobLevelId);

            $statusCode = $result['status_code'] ?? ($result['success'] ? 200 : 404);

            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching programs: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get budget items filtered by workplan (program) ID
     */
    public function getBudgetItems($programId)
    {
        try {
            $result = $this->submissionService->getBudgetItemsByProgram((int) $programId);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching budget items: '.$e->getMessage(),
            ], 500);
        }
    }

    /* ========================
        APPROVAL ACTIONS
    ======================== */

    /**
     * Approve transaction using dynamic approval system
     */
    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comments' => 'nullable|string|max:500',
            'detail_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->submissionService->processApprovalAction(
                (int) $id,
                'approve',
                $request->input('comments')
            );

            $statusCode = $result['status_code'] ?? ($result['success'] ? 200 : 400);

            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            $this->logService->create($e->getMessage(), [
                'Class' => SubmissionController::class,
                'Function' => 'approve',
                'User ID' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error approving transaction: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject transaction using dynamic approval system
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comments' => 'required|string|max:500',
            'detail_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->submissionService->processApprovalAction(
                (int) $id,
                'reject',
                $request->input('comments')
            );

            $statusCode = $result['status_code'] ?? ($result['success'] ? 200 : 400);

            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            Log::error('Error rejecting transaction: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error rejecting transaction: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getBadgeInfo($id)
    {
        try {
            $result = $this->submissionService->getApprovalBadgeHtml((int) $id);

            $statusCode = $result['status_code'] ?? ($result['success'] ? 200 : 404);

            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            Log::error('Error fetching badge info: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching approval timeline',
            ], 404);
        }
    }

    public function viewPdf($id)
    {
        $data = $this->submissionService->generatePdfData((int) $id);

        $pdf = Pdf::loadView('pages.submission.pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream('budget-proposal-preview.pdf');
    }

    /**
     * Get approval status for a transaction
     */
    public function getApprovalStatus($id)
    {
        try {
            $result = $this->approvalTransactionService->getApprovalStatus((int) $id);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error fetching approval status: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching approval status',
            ], 500);
        }
    }

    /**
     * Get pending approvals for current user
     */
    public function getPendingApprovals()
    {
        try {
            $user = Auth::user();
            $employment = $user->employment;

            if (! $employment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employment data not found',
                    'data' => [],
                    'count' => 0,
                ]);
            }

            $result = $this->approvalTransactionService->getPendingApprovalsForUser($employment->id);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error fetching pending approvals: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching pending approvals',
                'data' => [],
                'count' => 0,
            ], 500);
        }
    }

    /**
     * Get approval counts for all tabs (pending, approved, rejected)
     */
    public function getApprovalCounts(Request $request)
    {
        try {
            $user = Auth::user();
            $employment = $user->employment;

            if (! $employment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employment data not found',
                ], 404);
            }

            $result = $this->approvalTransactionService->getApprovalCounts(
                $employment->id,
                ['year' => $request->input('year')]
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error fetching approval counts: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching approval counts',
            ], 500);
        }
    }

    /**
     * Get approval data for specific tab (pending, approved, rejected)
     */
    public function getApprovalData(Request $request)
    {
        try {
            $user = Auth::user();
            $employment = $user->employment;

            if (! $employment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employment data not found',
                ], 404);
            }

            $result = $this->approvalTransactionService->getApprovalItemsByStatus(
                $employment->id,
                $request->input('status'),
                [
                    'year' => $request->input('year'),
                    'search' => $request->input('search'),
                    'page' => $request->input('page', 1),
                    'per_page' => $request->input('per_page', 10),
                ]
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error fetching approval data: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching approval data: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel approval request for a transaction
     */
    public function cancelApproval($id)
    {
        try {
            $result = $this->submissionService->cancelApprovalRequest((int) $id);

            $statusCode = $result['status_code'] ?? ($result['success'] ? 200 : 403);

            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            Log::error('Error cancelling approval: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error cancelling approval: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resubmit transaction for approval
     */
    public function resubmitForApproval($id)
    {
        try {
            $result = $this->submissionService->resubmitForApproval((int) $id);

            $statusCode = $result['status_code'] ?? ($result['success'] ? 200 : 400);

            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            Log::error('Error resubmitting for approval: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error resubmitting for approval: '.$e->getMessage(),
            ], 500);
        }
    }

    /* ========================
        LPJ METHODS
    ======================== */

    /**
     * Get LPJ form data for a transaction
     */
    public function getLpjFormData($id)
    {
        try {
            $result = $this->lpjService->getLpjFormData($id);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error getting LPJ form data: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error getting LPJ form data: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit LPJ for a transaction
     */
    public function submitLpj(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'submission_date' => 'required|date',
            'realization_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.detail_id' => 'required|integer',
            'items.*.fix_quantity' => 'required|numeric|min:0',
            'items.*.fix_price' => 'required|numeric|min:0',
            'proof_of_payment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $request->only(['submission_date', 'realization_date', 'items']);

            if ($request->hasFile('proof_of_payment')) {
                $data['proof_of_payment'] = $request->file('proof_of_payment');
            }

            $result = $this->lpjService->submitLpj($id, $data);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error submitting LPJ: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error submitting LPJ: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get LPJ details by transaction ID
     */
    public function getLpjByTransaction($id)
    {
        try {
            $result = $this->lpjService->getLpjByTransactionId($id);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error getting LPJ: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error getting LPJ: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve LPJ
     */
    public function approveLpj(Request $request, $lpjId)
    {
        try {
            $user = Auth::user();
            $employment = $user->employment;

            if (! $employment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employment data not found',
                ], 400);
            }

            $notes = $request->input('notes');
            $result = $this->lpjService->processApproval($lpjId, 'approve', $employment->id, $notes);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error approving LPJ: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error approving LPJ: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject LPJ
     */
    public function rejectLpj(Request $request, $lpjId)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Rejection reason is required',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $employment = $user->employment;

            if (! $employment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employment data not found',
                ], 400);
            }

            $result = $this->lpjService->processApproval($lpjId, 'reject', $employment->id, $request->input('reason'));

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error rejecting LPJ: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error rejecting LPJ: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending LPJ approvals for current user
     */
    public function getPendingLpjApprovals()
    {
        try {
            $user = Auth::user();
            $employment = $user->employment;

            if (! $employment) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'count' => 0,
                ]);
            }

            $result = $this->lpjService->getPendingLpjApprovalsForUser($employment->id);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error getting pending LPJ approvals: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error getting pending LPJ approvals: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get LPJ approval counts for current user
     */
    public function getLpjApprovalCounts()
    {
        try {
            $user = Auth::user();
            $employment = $user->employment;

            if (! $employment) {
                return response()->json([
                    'success' => true,
                    'data' => ['pending' => 0, 'approved' => 0, 'rejected' => 0],
                ]);
            }

            $result = $this->lpjService->getLpjApprovalCounts($employment->id);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error getting LPJ approval counts: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error getting LPJ approval counts: '.$e->getMessage(),
            ], 500);
        }
    }
}
