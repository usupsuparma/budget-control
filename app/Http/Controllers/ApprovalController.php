<?php

namespace App\Http\Controllers;

use App\Models\ApprovalFlowDetail;
use App\Models\ApprovalFlowTemplate;
use App\Models\ApprovalModule;
use App\Models\Employee;
use App\Models\Employment;
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
            Log::error('Get pending approvals failed: '.$e->getMessage());

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
            Log::error('Get all transactions failed: '.$e->getMessage());

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
                'logs',
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
            Log::error('Get approval detail failed: '.$e->getMessage());

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
                'logs',
            ])->findOrFail($transactionId);

            return response()->json([
                'success' => true,
                'data' => $transaction,
            ]);
        } catch (\Exception $e) {
            Log::error('Get transaction detail failed: '.$e->getMessage());

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
                $user->first_name.' '.($user->last_name ?? ''),
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
            Log::error('Approve failed: '.$e->getMessage());

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
                $user->first_name.' '.($user->last_name ?? ''),
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
            Log::error('Reject failed: '.$e->getMessage());

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
                $user->first_name.' '.($user->last_name ?? ''),
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
            Log::error('Cancel transaction failed: '.$e->getMessage());

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
            Log::error('Check threshold failed: '.$e->getMessage());

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
            Log::error('Get statistics failed: '.$e->getMessage());

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
            Log::error('Get history failed: '.$e->getMessage());

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
            Log::error('Store threshold failed: '.$e->getMessage());

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
            Log::error('Update threshold failed: '.$e->getMessage());

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
            Log::error('Delete threshold failed: '.$e->getMessage());

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
            Log::error('Store authorizer failed: '.$e->getMessage());

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
            Log::error('Update authorizer failed: '.$e->getMessage());

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
            Log::error('Delete authorizer failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete authorizer',
            ], 500);
        }
    }

    // ========== NEW: Approval Modules Management ==========

    /**
     * Get all approval modules
     */
    public function getModules()
    {
        try {
            $modules = ApprovalModule::orderBy('module_name')->get();

            return response()->json([
                'success' => true,
                'data' => $modules,
            ]);
        } catch (\Exception $e) {
            Log::error('Get modules failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get modules',
            ], 500);
        }
    }

    /**
     * Get available tables for new module creation
     */
    public function getAvailableTables(Request $request)
    {
        try {
            $excludeId = $request->input('exclude_id');

            if ($excludeId) {
                $tables = ApprovalModule::getAvailableTablesForEdit((int) $excludeId);
            } else {
                $tables = ApprovalModule::getAvailableTables();
            }

            return response()->json([
                'success' => true,
                'data' => $tables,
            ]);
        } catch (\Exception $e) {
            Log::error('Get available tables failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get available tables',
            ], 500);
        }
    }

    /**
     * Get modules for dropdown (filter out modules already used in templates)
     */
    public function getModulesForDropdown(Request $request)
    {
        try {
            $excludeTemplateId = $request->input('exclude_template_id');
            
            // Get all active modules
            $allModules = ApprovalModule::where('is_active', true)
                ->orderBy('module_name')
                ->get();
            
            // Get module IDs that are already used in templates
            $usedModuleIds = ApprovalFlowTemplate::when($excludeTemplateId, function($query) use ($excludeTemplateId) {
                    return $query->where('id', '!=', $excludeTemplateId);
                })
                ->pluck('module_id')
                ->toArray();
            
            // Filter out used modules
            $availableModules = $allModules->filter(function($module) use ($usedModuleIds) {
                return !in_array($module->id, $usedModuleIds);
            })->values();
            
            return response()->json([
                'success' => true,
                'data' => $availableModules,
            ]);
        } catch (\Exception $e) {
            Log::error('Get modules for dropdown failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get modules for dropdown',
            ], 500);
        }
    }

    /**
     * Store new approval module
     */
    public function storeModule(Request $request)
    {
        // Convert string booleans to actual booleans
        $request->merge([
            'is_active' => filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
        ]);

        $allowedTables = array_keys(ApprovalModule::ALLOWED_TABLES);

        $validator = Validator::make($request->all(), [
            'module_name' => 'required|string|max:50|unique:approval_modules,module_name',
            'table_name' => 'required|string|in:'.implode(',', $allowedTables).'|unique:approval_modules,table_name',
            'is_active' => 'boolean',
        ], [
            'table_name.in' => 'Table yang dipilih tidak valid.',
            'table_name.unique' => 'Module untuk table ini sudah ada.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $module = ApprovalModule::create([
                'module_name' => $request->input('module_name'),
                'table_name' => $request->input('table_name'),
                'is_active' => $request->input('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Module created successfully',
                'data' => $module,
            ]);
        } catch (\Exception $e) {
            Log::error('Store module failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create module',
            ], 500);
        }
    }

    /**
     * Update approval module
     */
    public function updateModule(Request $request, $id)
    {
        // Convert string booleans to actual booleans
        $request->merge([
            'is_active' => filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
        ]);

        $allowedTables = array_keys(ApprovalModule::ALLOWED_TABLES);

        $validator = Validator::make($request->all(), [
            'module_name' => 'required|string|max:50|unique:approval_modules,module_name,'.$id,
            'table_name' => 'required|string|in:'.implode(',', $allowedTables).'|unique:approval_modules,table_name,'.$id,
            'is_active' => 'boolean',
        ], [
            'table_name.in' => 'Table yang dipilih tidak valid.',
            'table_name.unique' => 'Module untuk table ini sudah ada.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $module = ApprovalModule::findOrFail($id);
            $module->update([
                'module_name' => $request->input('module_name'),
                'table_name' => $request->input('table_name'),
                'is_active' => $request->input('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Module updated successfully',
                'data' => $module,
            ]);
        } catch (\Exception $e) {
            Log::error('Update module failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update module',
            ], 500);
        }
    }

    /**
     * Delete approval module
     */
    public function deleteModule($id)
    {
        try {
            $module = ApprovalModule::findOrFail($id);

            // Cek apakah modul memiliki template yang terkait
            if ($module->templates()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete module. It has associated templates.',
                ], 400);
            }

            $module->delete();

            return response()->json([
                'success' => true,
                'message' => 'Module deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete module failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete module',
            ], 500);
        }
    }

    // ========== NEW: Flow Templates Management ==========

    /**
     * Get all flow templates with module relation
     */
    public function getTemplates()
    {
        try {
            $templates = ApprovalFlowTemplate::with('module')
                ->orderBy('priority')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $templates,
            ]);
        } catch (\Exception $e) {
            Log::error('Get templates failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get templates',
            ], 500);
        }
    }

    /**
     * Store new flow template
     */
    public function storeTemplate(Request $request)
    {
        // Convert string booleans to actual booleans
        $request->merge([
            'use_uppline_chain' => filter_var($request->input('use_uppline_chain', false), FILTER_VALIDATE_BOOLEAN),
            'use_threshold' => filter_var($request->input('use_threshold', false), FILTER_VALIDATE_BOOLEAN),
            'is_active' => filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
        ]);

        $validator = Validator::make($request->all(), [
            'module_id' => 'required|exists:approval_modules,id|unique:approval_flow_templates,module_id',
            'template_name' => 'required|string|max:100',
            'use_uppline_chain' => 'boolean',
            'use_threshold' => 'boolean',
            'condition_field' => 'nullable|string|max:50',
            'priority' => 'integer|min:1',
            'is_active' => 'boolean',
        ], [
            'module_id.required' => 'Module harus dipilih.',
            'module_id.exists' => 'Module tidak valid.',
            'module_id.unique' => 'Module sudah memiliki template approval. Setiap module hanya boleh memiliki satu template.',
            'template_name.required' => 'Template name harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Get condition_field from selected module
            $module = ApprovalModule::findOrFail($request->input('module_id'));
            
            $template = ApprovalFlowTemplate::create([
                'module_id' => $request->input('module_id'),
                'template_name' => $request->input('template_name'),
                'use_uppline_chain' => $request->input('use_uppline_chain', false),
                'use_threshold' => $request->input('use_threshold', false),
                'condition_field' => $module->condition_field,
                'priority' => $request->input('priority', 1),
                'is_active' => $request->input('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template created successfully',
                'data' => $template->load('module'),
            ]);
        } catch (\Exception $e) {
            Log::error('Store template failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create template',
            ], 500);
        }
    }

    /**
     * Update flow template
     */
    public function updateTemplate(Request $request, $id)
    {
        // Convert string booleans to actual booleans
        $request->merge([
            'use_uppline_chain' => filter_var($request->input('use_uppline_chain', false), FILTER_VALIDATE_BOOLEAN),
            'use_threshold' => filter_var($request->input('use_threshold', false), FILTER_VALIDATE_BOOLEAN),
            'is_active' => filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
        ]);

        // NOTE: module_id is NOT included - cannot be changed on update
        $validator = Validator::make($request->all(), [
            'template_name' => 'required|string|max:100',
            'use_uppline_chain' => 'boolean',
            'use_threshold' => 'boolean',
            'priority' => 'integer|min:1',
            'is_active' => 'boolean',
        ], [
            'template_name.required' => 'Template name harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $template = ApprovalFlowTemplate::findOrFail($id);
            
            // Get condition_field from existing module (module cannot be changed)
            $module = ApprovalModule::findOrFail($template->module_id);
            
            // Update template - module_id is NOT updated
            $template->update([
                'template_name' => $request->input('template_name'),
                'use_uppline_chain' => $request->input('use_uppline_chain', false),
                'use_threshold' => $request->input('use_threshold', false),
                'condition_field' => $module->condition_field,
                'priority' => $request->input('priority', 1),
                'is_active' => $request->input('is_active', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully',
                'data' => $template->load('module'),
            ]);
        } catch (\Exception $e) {
            Log::error('Update template failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update template',
            ], 500);
        }
    }

    /**
     * Delete flow template
     */
    public function deleteTemplate($id)
    {
        try {
            $template = ApprovalFlowTemplate::findOrFail($id);

            // Cek apakah template memiliki details yang terkait
            if ($template->details()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete template. It has associated flow details.',
                ], 400);
            }

            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete template failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete template',
            ], 500);
        }
    }

    // ========== NEW: Flow Details Management ==========

    /**
     * Get flow details for a specific template
     */
    public function getFlowDetails($templateId)
    {
        try {
            $details = ApprovalFlowDetail::with(['template', 'employment.employee'])
                ->where('template_id', $templateId)
                ->orderBy('level_sequence')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $details,
            ]);
        } catch (\Exception $e) {
            Log::error('Get flow details failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get flow details',
            ], 500);
        }
    }

    /**
     * Store new flow detail
     */
    public function storeFlowDetail(Request $request)
    {
        // Convert string booleans to actual booleans
        $request->merge([
            'is_required' => filter_var($request->input('is_required', true), FILTER_VALIDATE_BOOLEAN),
        ]);

        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:approval_flow_templates,id',
            'level_sequence' => 'required|integer|min:1',
            'employment_id' => 'required|exists:employment,id',
            'threshold_amount' => 'nullable|numeric|min:0',
            'is_required' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $detail = ApprovalFlowDetail::create([
                'template_id' => $request->input('template_id'),
                'level_sequence' => $request->input('level_sequence'),
                'employment_id' => $request->input('employment_id'),
                'threshold_amount' => $request->input('threshold_amount'),
                'is_required' => $request->input('is_required', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Flow detail created successfully',
                'data' => $detail->load(['template', 'employment.employee']),
            ]);
        } catch (\Exception $e) {
            Log::error('Store flow detail failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create flow detail',
            ], 500);
        }
    }

    /**
     * Update flow detail
     */
    public function updateFlowDetail(Request $request, $id)
    {
        // Convert string booleans to actual booleans
        $request->merge([
            'is_required' => filter_var($request->input('is_required', true), FILTER_VALIDATE_BOOLEAN),
        ]);

        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:approval_flow_templates,id',
            'level_sequence' => 'required|integer|min:1',
            'employment_id' => 'required|exists:employment,id',
            'threshold_amount' => 'nullable|numeric|min:0',
            'is_required' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $detail = ApprovalFlowDetail::findOrFail($id);
            $employment = Employment::findOrFail($request->input('employment_id'));
            $name = $employment->employee->name;
            $detail->update([
                'template_id' => $request->input('template_id'),
                'level_sequence' => $request->input('level_sequence'),
                'employment_id' => $request->input('employment_id'),
                'threshold_amount' => $request->input('threshold_amount'),
                'is_required' => $request->input('is_required', true),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Flow detail updated successfully',
                'data' => $detail->load(['template', 'employment.employee']),
            ]);
        } catch (\Exception $e) {
            Log::error('Update flow detail failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update flow detail',
            ], 500);
        }
    }

    /**
     * Delete flow detail
     */
    public function deleteFlowDetail($id)
    {
        try {
            $detail = ApprovalFlowDetail::findOrFail($id);
            $detail->delete();

            return response()->json([
                'success' => true,
                'message' => 'Flow detail deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete flow detail failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete flow detail',
            ], 500);
        }
    }

    // ========== NEW: Helper - Get Employments ==========

    /**
     * Get all employments for dropdown
     */
    public function getEmployments()
    {
        try {
            // Debug: Log the total count first
            $totalEmployments = Employment::count();
            $employmentsWithEmployee = Employment::whereHas('employee')->count();
            Log::info("Employment Stats - Total: {$totalEmployments}, With Employee: {$employmentsWithEmployee}");

            $employments = Employment::with('employee')
                ->whereHas('employee')
                ->get()
                ->map(function ($employment) {
                    $employee = $employment->employee;
                    $employeeName = 'N/A';

                    if ($employee) {
                        // Try using name accessor first, fallback to manual concat
                        $employeeName = $employee->name ??
                            (($employee->first_name ?? '').' '.($employee->last_name ?? ''));
                        $employeeName = trim($employeeName);

                        // Add job position if available
                        if (! empty($employment->job_position_name)) {
                            $employeeName .= ' ('.$employment->job_position_name.')';
                        }
                    }

                    return [
                        'id' => $employment->id,
                        'employee_name' => $employeeName ?: 'Unknown Employee',
                        'employee_id' => $employment->employee_id,
                        'job_position' => $employment->job_position_name ?? null,
                        'organization' => $employment->organization_name ?? null,
                    ];
                })
                ->sortBy('employee_name')
                ->values();

            Log::info('Returning '.count($employments).' employments');

            return response()->json([
                'success' => true,
                'data' => $employments,
            ]);
        } catch (\Exception $e) {
            Log::error('Get employments failed: '.$e->getMessage().' | Trace: '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get employments: '.$e->getMessage(),
            ], 500);
        }
    }

    // ========== NEW: Uppline Configs Management ==========

    /**
     * Get uppline configs for a template
     */
    public function getUpplineConfigs($templateId)
    {
        try {
            $configs = \App\Models\ApprovalFlowUpplineConfigs::where('template_id', $templateId)
                ->orderBy('step_sequence', 'asc')
                ->get()
                ->map(function ($config) {
                    return [
                        'id' => $config->id,
                        'template_id' => $config->template_id,
                        'division_id' => $config->division_id,
                        'division_name' => $config->division_id 
                            ? optional(\App\Models\Division::find($config->division_id))->name 
                            : 'Default (All Division)',
                        'step_sequence' => $config->step_sequence,
                        'job_level_name' => $config->job_level_name,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $configs,
            ]);
        } catch (\Exception $e) {
            Log::error('Get uppline configs failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get uppline configs',
            ], 500);
        }
    }

    /**
     * Store new uppline config
     */
    public function storeUpplineConfig(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:approval_flow_templates,id',
            'division_id' => 'nullable|exists:division,id',
            'step_sequence' => 'required|integer|min:1',
            'job_level_name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Check for duplicate step_sequence in same template+division
            $existing = \App\Models\ApprovalFlowUpplineConfigs::where('template_id', $request->template_id)
                ->where('step_sequence', $request->step_sequence)
                ->where(function($query) use ($request) {
                    if ($request->division_id) {
                        $query->where('division_id', $request->division_id);
                    } else {
                        $query->whereNull('division_id');
                    }
                })
                ->exists();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Step sequence already exists for this template and division',
                ], 422);
            }

            $config = \App\Models\ApprovalFlowUpplineConfigs::create([
                'template_id' => $request->template_id,
                'division_id' => $request->division_id,
                'step_sequence' => $request->step_sequence,
                'job_level_name' => $request->job_level_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Uppline config created successfully',
                'data' => $config,
            ]);
        } catch (\Exception $e) {
            Log::error('Store uppline config failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create uppline config',
            ], 500);
        }
    }

    /**
     * Update uppline config
     */
    public function updateUpplineConfig(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'nullable|exists:division,id',
            'step_sequence' => 'required|integer|min:1',
            'job_level_name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $config = \App\Models\ApprovalFlowUpplineConfigs::findOrFail($id);

            // Check for duplicate step_sequence (excluding current record)
            $existing = \App\Models\ApprovalFlowUpplineConfigs::where('template_id', $config->template_id)
                ->where('step_sequence', $request->step_sequence)
                ->where('id', '!=', $id)
                ->where(function($query) use ($request) {
                    if ($request->division_id) {
                        $query->where('division_id', $request->division_id);
                    } else {
                        $query->whereNull('division_id');
                    }
                })
                ->exists();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Step sequence already exists for this template and division',
                ], 422);
            }

            $config->update([
                'division_id' => $request->division_id,
                'step_sequence' => $request->step_sequence,
                'job_level_name' => $request->job_level_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Uppline config updated successfully',
                'data' => $config,
            ]);
        } catch (\Exception $e) {
            Log::error('Update uppline config failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update uppline config',
            ], 500);
        }
    }

    /**
     * Delete uppline config
     */
    public function deleteUpplineConfig($id)
    {
        try {
            $config = \App\Models\ApprovalFlowUpplineConfigs::findOrFail($id);
            $config->delete();

            return response()->json([
                'success' => true,
                'message' => 'Uppline config deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete uppline config failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete uppline config',
            ], 500);
        }
    }

    /**
     * Get divisions for dropdown
     */
    public function getDivisions()
    {
        try {
            $divisions = \App\Models\Division::select('id', 'name as division_name')
                ->where('status', 'active')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $divisions,
            ]);
        } catch (\Exception $e) {
            Log::error('Get divisions failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get divisions',
            ], 500);
        }
    }

    /**
     * Get job levels for dropdown
     */
    public function getJobLevels()
    {
        try {
            $jobLevels = \App\Models\JobLevel::select('id', 'job_level_name')
                ->where('status', 'active')
                ->orderBy('id', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $jobLevels,
            ]);
        } catch (\Exception $e) {
            Log::error('Get job levels failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get job levels',
            ], 500);
        }
    }
}
