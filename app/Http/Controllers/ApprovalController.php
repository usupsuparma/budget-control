<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Transaction;
use App\Models\TransactionApproval;
use App\Models\TransactionApprovalThreshold;
use App\Models\TransactionAuthorizer;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ApprovalController extends Controller
{
    protected $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Display approval dashboard
     */
    public function index()
    {
        $title = 'Approval Management';
        $user = Auth::user();
        
        // Get statistics
        $stats = $this->approvalService->getApprovalStatistics($user->id);
        
        // Get pending approvals for current user
        $pendingApprovals = $this->approvalService->getPendingApprovalsForUser($user->id);
        
        return view('pages.approval.main', compact('title', 'stats', 'pendingApprovals'));
    }

    /**
     * Get pending approvals list (AJAX)
     */
    public function getPendingApprovals(Request $request)
    {
        try {
            $user = Auth::user();
            $approvals = $this->approvalService->getPendingApprovalsForUser($user->id);

            $data = $approvals->map(function ($approval) {
                return [
                    'id' => $approval->id,
                    'transaction_id' => $approval->transaction_id,
                    'transaction_date' => $approval->transaction->transaction_date,
                    'user_name' => $approval->transaction->user_name,
                    'purpose' => $approval->transaction->purpose,
                    'estimated_amount' => $approval->transaction->estimated_amount,
                    'urgency' => $approval->transaction->urgency,
                    'approval_level' => $approval->approval_level,
                    'sequence_order' => $approval->sequence_order,
                    'created_at' => $approval->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data->values(),
            ]);
        } catch (\Exception $e) {
            Log::error('Get pending approvals failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get pending approvals',
            ], 500);
        }
    }

    /**
     * Get all transactions for admin
     */
    public function getAllTransactions(Request $request)
    {
        try {
            $query = Transaction::with(['approvals', 'threshold']);

            // Filter by status
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // Filter by year
            if ($request->has('year') && $request->year !== '') {
                $query->whereYear('transaction_date', $request->year);
            }

            // Filter by month
            if ($request->has('month') && $request->month !== '') {
                $query->whereMonth('transaction_date', $request->month);
            }

            // Paginate
            $perPage = $request->input('per_page', 10);
            $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $transactions,
            ]);
        } catch (\Exception $e) {
            Log::error('Get all transactions failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get transactions',
            ], 500);
        }
    }

    /**
     * Get approval detail
     */
    public function show($id)
    {
        try {
            $approval = TransactionApproval::with([
                'transaction.approvals',
                'transaction.threshold',
                'logs'
            ])->findOrFail($id);

            $history = $this->approvalService->getApprovalHistory($approval->transaction_id);
            $logs = $this->approvalService->getApprovalLogs($approval->transaction_id);

            return response()->json([
                'success' => true,
                'data' => [
                    'approval' => $approval,
                    'history' => $history,
                    'logs' => $logs,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Get approval detail failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get approval detail',
            ], 500);
        }
    }

    /**
     * Get transaction detail with approvals
     */
    public function getTransactionDetail($transactionId)
    {
        try {
            $transaction = Transaction::with([
                'approvals' => function ($query) {
                    $query->orderBy('sequence_order');
                },
                'threshold',
                'logs'
            ])->findOrFail($transactionId);

            return response()->json([
                'success' => true,
                'data' => $transaction,
            ]);
        } catch (\Exception $e) {
            Log::error('Get transaction detail failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get transaction detail',
            ], 500);
        }
    }

    /**
     * Process approval (approve)
     */
    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comments' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            
            $result = $this->approvalService->processApproval(
                $id,
                TransactionApproval::STATUS_APPROVED,
                $user->id,
                $user->first_name . ' ' . ($user->last_name ?? ''),
                $request->input('comments'),
                $request->ip()
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => [
                        'transaction' => $result['transaction'] ?? null,
                        'is_fully_approved' => $result['is_fully_approved'] ?? false,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);

        } catch (\Exception $e) {
            Log::error('Approve failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process approval',
            ], 500);
        }
    }

    /**
     * Process approval (reject)
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comments' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            
            $result = $this->approvalService->processApproval(
                $id,
                TransactionApproval::STATUS_REJECTED,
                $user->id,
                $user->first_name . ' ' . ($user->last_name ?? ''),
                $request->input('comments'),
                $request->ip()
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => [
                        'transaction' => $result['transaction'] ?? null,
                        'rejected_by_level' => $result['rejected_by_level'] ?? null,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);

        } catch (\Exception $e) {
            Log::error('Reject failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process rejection',
            ], 500);
        }
    }

    /**
     * Cancel transaction
     */
    public function cancel(Request $request, $transactionId)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            
            $result = $this->approvalService->cancelTransaction(
                $transactionId,
                $user->id,
                $user->first_name . ' ' . ($user->last_name ?? ''),
                $request->input('reason')
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result['transaction'] ?? null,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);

        } catch (\Exception $e) {
            Log::error('Cancel transaction failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel transaction',
            ], 500);
        }
    }

    /**
     * Check threshold for amount
     */
    public function checkThreshold(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $threshold = $this->approvalService->determineApprovalFlow($request->input('amount'));

            if ($threshold) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'threshold' => $threshold,
                        'approval_levels_required' => $threshold->approval_sequence,
                        'required_levels' => $threshold->required_levels,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No threshold found for this amount',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Check threshold failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check threshold',
            ], 500);
        }
    }

    /**
     * Get approval statistics
     */
    public function getStatistics()
    {
        try {
            $user = Auth::user();
            $stats = $this->approvalService->getApprovalStatistics($user->id);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Get statistics failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics',
            ], 500);
        }
    }

    /**
     * Get approval history for a transaction
     */
    public function getHistory($transactionId)
    {
        try {
            $history = $this->approvalService->getApprovalHistory($transactionId);
            $logs = $this->approvalService->getApprovalLogs($transactionId);

            return response()->json([
                'success' => true,
                'data' => [
                    'approvals' => $history,
                    'logs' => $logs,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Get history failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get approval history',
            ], 500);
        }
    }

    // ========== Threshold Management ==========

    /**
     * List all thresholds
     */
    public function thresholdIndex()
    {
        $title = 'Approval Thresholds';
        $thresholds = TransactionApprovalThreshold::orderBy('min_amount')->get();
        return view('pages.approval.threshold', compact('title', 'thresholds'));
    }

    /**
     * Get thresholds data (AJAX)
     */
    public function getThresholds()
    {
        try {
            $thresholds = TransactionApprovalThreshold::orderBy('min_amount')->get();
            return response()->json([
                'success' => true,
                'data' => $thresholds,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get thresholds',
            ], 500);
        }
    }

    /**
     * Store new threshold
     */
    public function storeThreshold(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'approval_sequence' => 'required|integer|min:1|max:5',
            'required_levels' => 'required|array|min:1',
            'required_levels.*' => 'integer|min:1|max:5',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $threshold = TransactionApprovalThreshold::create([
                'min_amount' => $request->input('min_amount'),
                'max_amount' => $request->input('max_amount'),
                'approval_sequence' => $request->input('approval_sequence'),
                'required_levels' => $request->input('required_levels'),
                'description' => $request->input('description'),
                'is_active' => $request->input('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Threshold created successfully',
                'data' => $threshold,
            ]);
        } catch (\Exception $e) {
            Log::error('Store threshold failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create threshold',
            ], 500);
        }
    }

    /**
     * Update threshold
     */
    public function updateThreshold(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'approval_sequence' => 'required|integer|min:1|max:5',
            'required_levels' => 'required|array|min:1',
            'required_levels.*' => 'integer|min:1|max:5',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $threshold = TransactionApprovalThreshold::findOrFail($id);
            $threshold->update([
                'min_amount' => $request->input('min_amount'),
                'max_amount' => $request->input('max_amount'),
                'approval_sequence' => $request->input('approval_sequence'),
                'required_levels' => $request->input('required_levels'),
                'description' => $request->input('description'),
                'is_active' => $request->input('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Threshold updated successfully',
                'data' => $threshold,
            ]);
        } catch (\Exception $e) {
            Log::error('Update threshold failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update threshold',
            ], 500);
        }
    }

    /**
     * Delete threshold
     */
    public function deleteThreshold($id)
    {
        try {
            $threshold = TransactionApprovalThreshold::findOrFail($id);
            $threshold->delete();

            return response()->json([
                'success' => true,
                'message' => 'Threshold deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete threshold failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete threshold',
            ], 500);
        }
    }

    // ========== Authorizer Management ==========

    /**
     * List all authorizers
     */
    public function authorizerIndex()
    {
        $title = 'Approval Authorizers';
        $authorizers = TransactionAuthorizer::with('employee')->orderBy('approval_level')->orderBy('priority_order')->get();
        $employees = Employee::orderBy('first_name')->get();
        return view('pages.approval.authorizer', compact('title', 'authorizers', 'employees'));
    }

    /**
     * Get authorizers data (AJAX)
     */
    public function getAuthorizers()
    {
        try {
            $authorizers = TransactionAuthorizer::with('employee')
                ->orderBy('approval_level')
                ->orderBy('priority_order')
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $authorizers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get authorizers',
            ], 500);
        }
    }

    /**
     * Store new authorizer
     */
    public function storeAuthorizer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employee,id',
            'authorizer_name' => 'required|string|max:255',
            'level_number' => 'required|integer|min:1',
            'authority' => 'nullable|string|max:255',
            'position_code' => 'nullable|string|max:50',
            'approval_level' => 'required|integer|min:1|max:5',
            'max_approval_amount' => 'nullable|numeric|min:0',
            'can_override' => 'boolean',
            'priority_order' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $authorizer = TransactionAuthorizer::create([
                'employee_id' => $request->input('employee_id'),
                'authorizer_name' => $request->input('authorizer_name'),
                'level_number' => $request->input('level_number'),
                'authority' => $request->input('authority'),
                'position_code' => $request->input('position_code'),
                'approval_level' => $request->input('approval_level'),
                'max_approval_amount' => $request->input('max_approval_amount'),
                'can_override' => $request->input('can_override', false),
                'priority_order' => $request->input('priority_order', 1),
                'status' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Authorizer created successfully',
                'data' => $authorizer,
            ]);
        } catch (\Exception $e) {
            Log::error('Store authorizer failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create authorizer',
            ], 500);
        }
    }

    /**
     * Update authorizer
     */
    public function updateAuthorizer(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employee,id',
            'authorizer_name' => 'required|string|max:255',
            'level_number' => 'required|integer|min:1',
            'authority' => 'nullable|string|max:255',
            'position_code' => 'nullable|string|max:50',
            'approval_level' => 'required|integer|min:1|max:5',
            'max_approval_amount' => 'nullable|numeric|min:0',
            'can_override' => 'boolean',
            'priority_order' => 'nullable|integer|min:1',
            'status' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $authorizer = TransactionAuthorizer::findOrFail($id);
            $authorizer->update([
                'employee_id' => $request->input('employee_id'),
                'authorizer_name' => $request->input('authorizer_name'),
                'level_number' => $request->input('level_number'),
                'authority' => $request->input('authority'),
                'position_code' => $request->input('position_code'),
                'approval_level' => $request->input('approval_level'),
                'max_approval_amount' => $request->input('max_approval_amount'),
                'can_override' => $request->input('can_override', false),
                'priority_order' => $request->input('priority_order', 1),
                'status' => $request->input('status', 1),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Authorizer updated successfully',
                'data' => $authorizer,
            ]);
        } catch (\Exception $e) {
            Log::error('Update authorizer failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update authorizer',
            ], 500);
        }
    }

    /**
     * Delete authorizer
     */
    public function deleteAuthorizer($id)
    {
        try {
            $authorizer = TransactionAuthorizer::findOrFail($id);
            $authorizer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Authorizer deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete authorizer failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete authorizer',
            ], 500);
        }
    }
}
