<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionApproval;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\JobPosition;
use App\Models\KPIWorkPlan;
use App\Models\BudgetCode;
use App\Models\Employment;
use App\Models\Unit;
use App\Models\WorkplanBudgetItem;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestDetail;
use App\Services\ApprovalService;
use App\Services\ApprovalTransactionService\ApprovalTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class SubmissionController extends Controller
{
    protected $approvalService;
    protected $approvalTransactionService;

    public function __construct(
        ApprovalService $approvalService,
        ApprovalTransactionService $approvalTransactionService
    ) {
        $this->approvalService = $approvalService;
        $this->approvalTransactionService = $approvalTransactionService;
    }

    public function user()
    {
        $title = 'Submission Users';

        // Get summary data (user_id = employee.id)
        $userId = Auth::user()->id;
        $employee = Auth::user();
        $employment = $employee->employment;
        $newSubmission = Transaction::where('user_id', $userId)->where('status', 0)->count();
        $progress = Transaction::where('user_id', $userId)->whereIn('status', [1, 2, 3, 4, 5])->count();
        $paid = Transaction::where('user_id', $userId)->where('status', 7)->count();
        $completion = Transaction::where('user_id', $userId)->where('status', 8)->count();
        $totalSubmission = Transaction::where('user_id', $userId)->count();

        // Get filter data
        $years = Transaction::selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $statuses = [
            ['value' => 0, 'label' => 'Submission'],
            ['value' => 1, 'label' => 'Approved Parent'],
            ['value' => 2, 'label' => 'Approved Finance'],
            ['value' => 3, 'label' => 'Approved Division'],
            ['value' => 4, 'label' => 'Approved Finance Director'],
            ['value' => 5, 'label' => 'Approved President Director'],
            ['value' => 6, 'label' => 'Rejected'],
            ['value' => 7, 'label' => 'Paid'],
            ['value' => 8, 'label' => 'Complete'],
            ['value' => -1, 'label' => 'Cancelled'],
        ];

        // Get dropdown data for modal
        $jobLevels = JobLevel::all();
        $jobPositions = JobPosition::all();
        $workplans = KPIWorkPlan::with(['kpiDepartment', 'kpiSection'])->get();
        $budgetCodes = WorkplanBudgetItem::with('budgetCodeRelation')->get();
        $units = Unit::all();

        return view('pages.submission.user', compact(
            'title',
            'newSubmission',
            'progress',
            'paid',
            'completion',
            'totalSubmission',
            'years',
            'statuses',
            'jobLevels',
            'jobPositions',
            'workplans',
            'budgetCodes',
            'units',
            'employment'
        ));
    }

    public function getSummary(Request $request)
    {
        try {
            $userId = Auth::id();
            
            $yearFilter = $request->filled('year') && $request->year !== 'all';

            $newSubmission = Transaction::where('user_id', $userId)
                ->whereIn('status', [0, 1, 2, 3, 4, 5])
                ->when($yearFilter, fn($q) => $q->whereYear('transaction_date', $request->year))
                ->count();

            $progress = Transaction::where('user_id', $userId)
                ->where('status', 7)
                ->when($yearFilter, fn($q) => $q->whereYear('transaction_date', $request->year))
                ->count();

            $paid = Transaction::where('user_id', $userId)
                ->where('status', 7)
                ->when($yearFilter, fn($q) => $q->whereYear('transaction_date', $request->year))
                ->count();

            $completion = Transaction::where('user_id', $userId)
                ->where('status', 8)
                ->when($yearFilter, fn($q) => $q->whereYear('transaction_date', $request->year))
                ->count();

            $rejected = Transaction::where('user_id', $userId)
                ->where('status', 6)
                ->when($yearFilter, fn($q) => $q->whereYear('transaction_date', $request->year))
                ->count();

            $totalSubmission = Transaction::where('user_id', $userId)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'newSubmission' => $newSubmission,
                    'progress' => $progress,
                    'paid' => $paid,
                    'completion' => $completion,
                    'rejected' => $rejected, 
                    'totalSubmission' => $totalSubmission
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching summary: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getData(Request $request)
    {
        try {
            $userId = Auth::id();
            $user = Auth::user();
            $employment = $user->employment;
            $employmentId = $employment ? $employment->id : null;

            $query = Transaction::query();
            $query->where('user_id', $userId);
            $query->with([
                'details',
                // Legacy approval system
                'approvals' => function($q) use ($userId) {
                    $q->where('approver_id', $userId)
                      ->where('status', 0); // pending
                },
                // New dynamic approval system
                'approvalRequest.details' => function($q) {
                    $q->orderBy('level_sequence');
                }
            ]);

            // Filter by year
            if ($request->has('year') && $request->year != '' && $request->year != 'all') {
                $query->whereYear('transaction_date', $request->year);
            }

            // Filter by status
            if($request->has('status') && $request->status == 'dis'){
                $query->whereIn('status', array(0,1,2,3,4,5));
            } elseif ($request->has('status') && $request->status !== '' && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Pagination
            $perPage = $request->per_page ?? 10;
            $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Add can_approve flag to each transaction
            $transactions->getCollection()->transform(function($transaction) use ($userId, $employmentId) {
                // Check legacy system first
                $transaction->can_approve = $transaction->approvals->isNotEmpty();
                $transaction->pending_approval = $transaction->approvals->first();

                // Check new dynamic approval system if employmentId exists
                if ($employmentId && $transaction->approvalRequest) {
                    $request = $transaction->approvalRequest;
                    if ($request->status === 'pending') {
                        // Find next pending detail
                        $nextPending = $request->details
                            ->where('status', 'pending')
                            ->sortBy('level_sequence')
                            ->first();

                        if ($nextPending && $nextPending->employment_id == $employmentId) {
                            $transaction->can_approve = true;
                            $transaction->pending_approval_detail = $nextPending;
                        }
                    }

                    // Add approval progress info
                    $transaction->approval_progress = [
                        'current_level' => $request->current_level,
                        'total_levels' => $request->total_levels,
                        'status' => $request->status,
                        'current_phase' => $request->current_phase,
                    ];
                }

                return $transaction;
            });

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error('Error fetching transactions: ' . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching transactions: ' . $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Log incoming request data for debugging
        Log::info('Store transaction request data:', $request->all());

        $validator = Validator::make($request->all(), [
            'transaction_date' => 'required|date',
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
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate budget values: ensure qty * price does not exceed budget value
        $budgetErrors = [];
        foreach ($request->items as $index => $item) {
            $budgetItem = WorkplanBudgetItem::find($item['budget_id']);
            
            if ($budgetItem) {
                $totalItemCost = $item['quantity'] * $item['price'];
                $budgetValue = $budgetItem->total ?? 0;
                
                if ($totalItemCost > $budgetValue) {
                    $budgetErrors[] = [
                        'item' => $item['goods_service_name'] ?? "Item " . ($index + 1),
                        'total' => 'Rp ' . number_format($totalItemCost, 0, ',', '.'),
                        'budget' => 'Rp ' . number_format($budgetValue, 0, ',', '.'),
                        'budget_code' => $budgetItem->budgetCodeRelation->name ?? 'Unknown'
                    ];
                }
            }
        }

        if (!empty($budgetErrors)) {
            $errorMessage = 'Budget validation failed. The following items exceed their budget values:';
            Log::error('Budget validation error in create transaction', ['errors' => $budgetErrors]);
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'budget_errors' => $budgetErrors
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $estimatedAmount = 0;

            // Calculate total estimated amount
            foreach ($request->items as $item) {
                $estimatedAmount += $item['quantity'] * $item['price'];
            }

            // Get unit info
            $unit = Unit::find($request->items[0]['unit_id']);

            // Create transaction
            $transaction = Transaction::create([
                'transaction_date' => $request->transaction_date,
                'user_id' => $user->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'unit_id' => $unit->id ?? 0,
                'unit_name' => $unit->unit ?? '',
                'job_level_id' => $request->job_level_id,
                'job_position_id' => $request->job_position_id,
                'program_id' => $request->program_id,
                'purpose' => $request->purpose,
                'estimated_amount' => $estimatedAmount,
                'actual_amount' => 0,
                'urgency' => $request->urgency,
                'status' => Transaction::STATUS_PENDING, // Pending - waiting for approval submission
            ]);

            // Create transaction details
            foreach ($request->items as $item) {
                $budgetItem = WorkplanBudgetItem::with('budgetCodeRelation')->find($item['budget_id']);
                $unit = Unit::find($item['unit_id']);
                $total = $item['quantity'] * $item['price'];

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'budget_id' => $budgetItem->id,
                    'budget_name' => $budgetItem->description ?? '',
                    'goods_service_name' => $item['goods_service_name'],
                    'balance' => $budgetItem->total ?? 0,
                    'estimated_price' => $item['price'],
                    'estimated_quantity' => $item['quantity'],
                    'estimated_total' => $total,
                    'fix_price' => 0,
                    'fix_quantity' => 0,
                    'fix_total' => 0,
                    'unit_id' => $unit->id,
                    'unit_name' => $unit->name,
                    'remark' => $item['remark'] ?? '',
                    'urgency' => $request->urgency,
                    'status' => 0,
                ]);
            }

            DB::commit();

            // Submit for approval using dynamic approval system
            $approvalResult = $this->approvalTransactionService->submitForApproval($transaction->id);
            
            if (!$approvalResult['success']) {
                Log::warning('Failed to submit for approval: ' . $approvalResult['message']);
                // Transaction is created but approval submission failed - still return success
                // The user can manually submit for approval later
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully' . ($approvalResult['success'] ? ' and submitted for approval.' : '. Note: ' . $approvalResult['message']),
                'data' => $transaction->load(['details', 'approvalRequest.details']),
                'approval' => $approvalResult
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $transaction = Transaction::with([
                'details',
                'approvals' => function($query) {
                    $query->orderBy('sequence_order', 'asc');
                },
                'approvalRequest.details' => function($query) {
                    $query->orderBy('phase', 'asc')
                          ->orderBy('level_sequence', 'asc');
                },
                'jobLevel',
                'jobPosition',
                'unit'
            ])->findOrFail($id);

            // Check if user owns this transaction OR is an approver
            $user = Auth::user();
            $isOwner = $transaction->user_id == $user->id;
            $isApprover = false;

            // Check if user is an approver for this transaction
            if (!$isOwner && $user->employment) {
                $employmentId = $user->employment->id;
                
                // Check in new dynamic approval system
                $isApprover = ApprovalRequestDetail::whereHas('request', function($q) use ($id) {
                    $q->where('reference_id', $id)
                      ->whereHas('module', fn($mq) => $mq->where('table_name', 'transactions'));
                })
                ->where('employment_id', $employmentId)
                ->exists();

                // Also check legacy system if not found
                if (!$isApprover) {
                    $isApprover = TransactionApproval::where('transaction_id', $id)
                        ->where('approver_id', $user->id)
                        ->exists();
                }
            }

            if (!$isOwner && !$isApprover) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'transaction_date' => 'required|date',
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
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate budget values: ensure qty * price does not exceed budget value
        $budgetErrors = [];
        foreach ($request->items as $index => $item) {
            $budgetItem = WorkplanBudgetItem::find($item['budget_id']);
            
            if ($budgetItem) {
                $totalItemCost = $item['quantity'] * $item['price'];
                $budgetValue = $budgetItem->value ?? 0;
                
                if ($totalItemCost > $budgetValue) {
                    $budgetErrors[] = [
                        'item' => $item['goods_service_name'] ?? "Item " . ($index + 1),
                        'total' => 'Rp ' . number_format($totalItemCost, 0, ',', '.'),
                        'budget' => 'Rp ' . number_format($budgetValue, 0, ',', '.'),
                        'budget_code' => $budgetItem->budgetCodeRelation->name ?? 'Unknown'
                    ];
                }
            }
        }

        if (!empty($budgetErrors)) {
            $errorMessage = 'Budget validation failed. The following items exceed their budget values:';
            Log::error('Budget validation error in update transaction', ['errors' => $budgetErrors]);
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'budget_errors' => $budgetErrors
            ], 422);
        }

        try {
            $transaction = Transaction::findOrFail($id);

            // Check if user owns this transaction
            if ($transaction->user_id != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Check if transaction can be edited (only status 0 - Submission)
            if ($transaction->status != 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction cannot be edited'
                ], 403);
            }

            DB::beginTransaction();

            $estimatedAmount = 0;

            // Calculate total estimated amount
            foreach ($request->items as $item) {
                $estimatedAmount += $item['quantity'] * $item['price'];
            }

            // Get unit info
            $unit = Unit::find($request->items[0]['unit_id']);

            // Update transaction
            $transaction->update([
                'transaction_date' => $request->transaction_date,
                'job_level_id' => $request->job_level_id,
                'job_position_id' => $request->job_position_id,
                'program_id' => $request->program_id,
                'purpose' => $request->purpose,
                'estimated_amount' => $estimatedAmount,
                'urgency' => $request->urgency,
                'unit_id' => $unit->id ?? 0,
                'unit_name' => $unit->name ?? '',
            ]);

            // Delete old details
            $transaction->details()->delete();

            // Create new transaction details
            foreach ($request->items as $item) {
                $budgetItem = WorkplanBudgetItem::with('budgetCodeRelation')->find($item['budget_id']);
                $unit = Unit::find($item['unit_id']);
                $total = $item['quantity'] * $item['price'];

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'budget_id' => $budgetItem->id,
                    'budget_name' => $budgetItem->description ?? '',
                    'goods_service_name' => $item['goods_service_name'],
                    'balance' => $budgetItem->total ?? 0,
                    'estimated_price' => $item['price'],
                    'estimated_quantity' => $item['quantity'],
                    'estimated_total' => $total,
                    'fix_price' => 0,
                    'fix_quantity' => 0,
                    'fix_total' => 0,
                    'unit_id' => $unit->id,
                    'unit_name' => $unit->name,
                    'remark' => $item['remark'] ?? '',
                    'urgency' => $request->urgency,
                    'status' => 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully',
                'data' => $transaction->load('details')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            // Check if user owns this transaction
            if ($transaction->user_id != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Check if transaction can be deleted (only status 0 - Submission)
            if ($transaction->status != 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction cannot be deleted'
                ], 403);
            }

            DB::beginTransaction();

            // Delete details
            $transaction->details()->delete();

            // Delete transaction
            $transaction->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBudgetInfo($budgetId)
    {
        try {
            $budgetItem = WorkplanBudgetItem::with('budgetCodeRelation')->findOrFail($budgetId);

            return response()->json([
                'success' => true,
                'data' => [
                    'budget_value' => $budgetItem->total,
                    'budget_name' => $budgetItem->description,
                    'budget_code' => $budgetItem->budget_code,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Budget not found'
            ], 404);
        }
    }

    public function user_create()
    {
        $title = 'Submission Users Create';
        return view('pages.submission.user_create', compact('title'));
    }

    public function approval()
    {
        $title = 'Approval Submission';
        $userId = Auth::user()->id; // employee.id

        // Get summary data (employment.employee_id = employee.id)
        $employment = Employment::where('employee_id', $userId)->get();
        $newSubmission = Transaction::where('user_id', $userId)->where('status', 0)->count();
        $progress = Transaction::where('user_id', $userId)->whereIn('status', [1, 2, 3, 4, 5])->count();
        $paid = Transaction::where('user_id', $userId)->where('status', 7)->count();
        $completion = Transaction::where('user_id', $userId)->where('status', 8)->count();
        $totalSubmission = Transaction::where('user_id', $userId)->count();

        // Get filter data
        $years = Transaction::selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $statuses = [
            ['value' => 0, 'label' => 'Submission'],
            ['value' => 1, 'label' => 'Approved Parent'],
            ['value' => 2, 'label' => 'Approved Finance'],
            ['value' => 3, 'label' => 'Approved Division'],
            ['value' => 4, 'label' => 'Approved Finance Director'],
            ['value' => 5, 'label' => 'Approved President Director'],
            ['value' => 6, 'label' => 'Rejected'],
            ['value' => 7, 'label' => 'Paid'],
            ['value' => 8, 'label' => 'Complete'],
            ['value' => -1, 'label' => 'Cancelled'],
        ];

        // Get dropdown data for modal
        $jobLevels = JobLevel::all();
        $jobPositions = JobPosition::all();
        $workplans = KPIWorkPlan::with(['kpiDepartment', 'kpiSection'])->get();
        $budgetCodes = WorkplanBudgetItem::with('budgetCodeRelation')->get();
        $units = Unit::all();

        return view('pages.submission.approval', compact(
            'title',
            'newSubmission',
            'progress',
            'paid',
            'completion',
            'totalSubmission',
            'years',
            'statuses',
            'jobLevels',
            'jobPositions',
            'workplans',
            'budgetCodes',
            'units',
            'employment'
        ));
    }

    /**
     * Get job positions filtered by job level
     */
    public function getJobPositions($jobLevelId)
    {
        try {
            $jobPositions = JobPosition::where('job_level_id', $jobLevelId)
                ->orderBy('job_position_name')
                ->get(['id', 'job_position_name']);

            return response()->json([
                'success' => true,
                'data' => $jobPositions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching job positions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get programs (KPI Workplans) based on job level
     * If job level indicates 'section', get section KPIs
     * If job level indicates 'department', get department KPIs
     */
    public function getPrograms($jobLevelId)
    {
        try {
            // Get job level to determine kpi_type
            $jobLevel = JobLevel::find($jobLevelId);
            
            if (!$jobLevel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job level not found'
                ], 404);
            }

            // Determine kpi_type based on job level name or a specific field
            // You may need to adjust this logic based on your business rules
            $jobLevelName = strtolower($jobLevel->job_level_name);
            
            // If job level contains 'section' -> kpi_type = 'section'
            // If job level contains 'department' or 'manager' -> kpi_type = 'department'
            $kpiType = null;
            if (str_contains($jobLevelName, 'section') || str_contains($jobLevelName, 'staff')) {
                $kpiType = 'section';
            } elseif (str_contains($jobLevelName, 'department') || str_contains($jobLevelName, 'manager') || str_contains($jobLevelName, 'head')) {
                $kpiType = 'department';
            }

            if (!$kpiType) {
                // Default to both if cannot determine
                $workplans = KPIWorkPlan::with(['kpiDepartment.department', 'kpiSection.section'])
                    ->orderBy('year', 'desc')
                    ->orderBy('activity')
                    ->get();
            } else {
                $workplans = KPIWorkPlan::where('kpi_type', $kpiType)
                    ->with(['kpiDepartment.department', 'kpiSection.section'])
                    ->orderBy('year', 'desc')
                    ->orderBy('activity')
                    ->get();
            }

            // Format the data
            $formattedWorkplans = $workplans->map(function ($workplan) {
                $label = $workplan->activity . ' (' . $workplan->year . ')';
                
                if ($workplan->kpi_type === 'department' && $workplan->kpiDepartment) {
                    $label .= ' - ' . ($workplan->kpiDepartment->department->department_name ?? '');
                } elseif ($workplan->kpi_type === 'section' && $workplan->kpiSection) {
                    $label .= ' - ' . ($workplan->kpiSection->section->section_name ?? '');
                }

                return [
                    'id' => $workplan->id,
                    'activity' => $workplan->activity,
                    'year' => $workplan->year,
                    'kpi_type' => $workplan->kpi_type,
                    'label' => $label
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedWorkplans,
                'kpi_type' => $kpiType
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching programs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get budget items filtered by workplan (program) ID
     */
    public function getBudgetItems($programId)
    {
        try {
            $budgetItems = WorkplanBudgetItem::where('kpi_workplan_id', $programId)
                ->with(['budgetCodeRelation', 'category'])
                ->orderBy('description')
                ->get();

            // Format the data
            $formattedItems = $budgetItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'description' => $item->description,
                    'stock_code' => $item->stock_code,
                    'budget_code' => $item->budget_code,
                    'category_name' => $item->category->category_name ?? '',
                    'total' => $item->total,
                    'label' => $item->description . ' (' . ($item->stock_code ?? $item->budget_code) . ')'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedItems
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching budget items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve transaction using dynamic approval system
     */
    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comments' => 'nullable|string|max:500',
            'detail_id' => 'nullable|integer', // ApprovalRequestDetail ID for new system
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $employment = $user->employment;

            if (!$employment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employment data not found'
                ], 404);
            }

            // Try to find pending approval using new dynamic system
            $approvalRequest = ApprovalRequest::where('reference_id', $id)
                ->whereHas('module', fn($q) => $q->where('table_name', 'transactions'))
                ->where('status', 'pending')
                ->first();

            if ($approvalRequest) {
                // Use new dynamic approval system
                $pendingDetail = ApprovalRequestDetail::where('request_id', $approvalRequest->id)
                    ->where('employment_id', $employment->id)
                    ->where('status', 'pending')
                    ->first();

                if (!$pendingDetail) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses untuk approval ini atau approval sudah diproses.'
                    ], 404);
                }

                // Check if this is the next in sequence
                $nextPending = ApprovalRequestDetail::where('request_id', $approvalRequest->id)
                    ->where('status', 'pending')
                    ->orderBy('level_sequence')
                    ->first();

                if ($nextPending && $nextPending->id !== $pendingDetail->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Menunggu approval dari level sebelumnya.'
                    ], 400);
                }

                // Process using new service
                $result = $this->approvalTransactionService->processApproval(
                    $pendingDetail->id,
                    'approve',
                    $employment->id,
                    $request->comments
                );

                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'data' => $result
                ]);
            }

            // Fallback to old system for legacy transactions
            DB::beginTransaction();
            $userId = Auth::id();
            $userName = $user->first_name . ' ' . $user->last_name;

            $approval = TransactionApproval::where('transaction_id', $id)
                ->where('approver_id', $userId)
                ->where('status', 0)
                ->first();

            if (!$approval) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Approval not found or already processed'
                ], 404);
            }

            $result = $this->approvalService->processApproval(
                $approval->id,
                1,
                $userId,
                $userName,
                $request->comments,
                $request->ip()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving transaction: ' . $e->getMessage()
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
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $employment = $user->employment;

            if (!$employment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employment data not found'
                ], 404);
            }

            // Try to find pending approval using new dynamic system
            $approvalRequest = ApprovalRequest::where('reference_id', $id)
                ->whereHas('module', fn($q) => $q->where('table_name', 'transactions'))
                ->where('status', 'pending')
                ->first();

            if ($approvalRequest) {
                // Use new dynamic approval system
                $pendingDetail = ApprovalRequestDetail::where('request_id', $approvalRequest->id)
                    ->where('employment_id', $employment->id)
                    ->where('status', 'pending')
                    ->first();

                if (!$pendingDetail) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses untuk reject ini atau approval sudah diproses.'
                    ], 404);
                }

                // Check if this is the next in sequence
                $nextPending = ApprovalRequestDetail::where('request_id', $approvalRequest->id)
                    ->where('status', 'pending')
                    ->orderBy('level_sequence')
                    ->first();

                if ($nextPending && $nextPending->id !== $pendingDetail->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Menunggu approval dari level sebelumnya.'
                    ], 400);
                }

                // Process using new service
                $result = $this->approvalTransactionService->processApproval(
                    $pendingDetail->id,
                    'reject',
                    $employment->id,
                    $request->comments
                );

                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'data' => $result
                ]);
            }

            // Fallback to old system for legacy transactions
            DB::beginTransaction();
            $userId = Auth::id();
            $userName = $user->first_name . ' ' . $user->last_name;

            $approval = TransactionApproval::where('transaction_id', $id)
                ->where('approver_id', $userId)
                ->where('status', 0)
                ->first();

            if (!$approval) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Approval not found or already processed'
                ], 404);
            }

            $result = $this->approvalService->processApproval(
                $approval->id,
                2,
                $userId,
                $userName,
                $request->comments,
                $request->ip()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBadgeInfo($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);
            
            // Try to get timeline from new dynamic approval system first
            $timelineResult = $this->approvalTransactionService->getApprovalTimeline($id);
            
            if ($timelineResult['success'] && !empty($timelineResult['data'])) {
                $data = [];
                
                foreach ($timelineResult['data'] as $item) {
                    $iconClass = $item['badge_class'] ?? 'bg-secondary';
                    $badgeClass = $item['badge_class'] ?? 'bg-secondary';
                    
                    // Determine if this is a pending item
                    $isPending = ($item['status'] ?? '') === 'pending';
                    
                    if ($isPending) {
                        $data[] = '<div class="tt-item">
                            <div class="tt-icon bg-light">
                                <span class="tt-dot"></span>
                            </div>
                            <div class="tt-content">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <div class="fw-semibold"></div>
                                    <span class="badge rounded-pill bg-light text-muted">Pending</span>
                                </div>
                                <div class="small mt-1 text-muted">' . htmlspecialchars($item['label'] ?? '') . ' by <span class="fw-semibold">' . htmlspecialchars($item['approver_name'] ?? 'Waiting') . '</span></div>
                            </div>
                        </div>';
                    } else {
                        $data[] = '<div class="tt-item">
                            <div class="tt-icon ' . $iconClass . '">
                                <span class="tt-dot"></span>
                            </div>
                            <div class="tt-content">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <div class="fw-semibold">' . htmlspecialchars($item['date'] ?? '') . '</div>
                                    <span class="badge rounded-pill ' . $badgeClass . ' text-white">' . htmlspecialchars($item['label'] ?? '') . '</span>
                                </div>
                                <div class="small mt-1">' . htmlspecialchars($item['description'] ?? '') . '</div>
                            </div>
                        </div>';
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'data' => implode("", $data)
                ]);
            }

            // Fallback to old system for legacy transactions
            $transactionApproval = TransactionApproval::where('transaction_id', $id)
                ->orderBy('sequence_order', 'asc')
                ->get();

            $data = [];
            
            // Add submission entry
            $data[] = '<div class="tt-item">
                <div class="tt-icon bg-warning">
                    <span class="tt-dot"></span>
                </div>
                <div class="tt-content">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="fw-semibold">' . date("d M Y H:i:s", strtotime($transaction->created_at)) . '</div>
                        <span class="badge rounded-pill bg-warning text-dark">Submission</span>
                    </div>
                    <div class="small mt-1">Submission by <span class="fw-semibold">' . $transaction->user_name . '</span></div>
                </div>
            </div>';

            foreach ($transactionApproval as $buff2) {
                if ($buff2->status == 0) {
                    $approvalStatuses = [
                        3  => ['label' => 'Department',          'class' => 'bg-info'],
                        2  => ['label' => 'Division',            'class' => 'bg-info'],
                        1  => ['label' => 'Director',            'class' => 'bg-info'],
                        -1 => ['label' => 'Budget Control',      'class' => 'bg-info'],
                        -2 => ['label' => 'Finance Division',    'class' => 'bg-info'],
                        -3 => ['label' => 'Finance Director',    'class' => 'bg-info'],
                        -4 => ['label' => 'President Director',  'class' => 'bg-success'],
                    ];

                    $level = (int) $buff2->approval_level;
                    $status = $approvalStatuses[$level] ?? ['label' => 'Unknown', 'class' => 'bg-secondary'];

                    $data[] = '<div class="tt-item">
                        <div class="tt-icon bg-light">
                            <span class="tt-dot"></span>
                        </div>
                        <div class="tt-content">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="fw-semibold"></div>
                                <span class="badge rounded-pill bg-light text-muted">Pending</span>
                            </div>
                            <div class="small mt-1 text-muted">' . $status['label'] . ' by <span class="fw-semibold">' . $buff2->approver_name . '</span></div>
                        </div>
                    </div>';
                } else {
                    $approvalStatuses = [
                        3  => ['label' => 'Approved Department',          'class' => 'bg-info'],
                        2  => ['label' => 'Approved Division',            'class' => 'bg-info'],
                        1  => ['label' => 'Approved Director',            'class' => 'bg-info'],
                        -1 => ['label' => 'Approved Budget Control',      'class' => 'bg-info'],
                        -2 => ['label' => 'Approved Finance Division',    'class' => 'bg-info'],
                        -3 => ['label' => 'Approved Finance Director',    'class' => 'bg-info'],
                        -4 => ['label' => 'Approved President Director',  'class' => 'bg-success'],
                    ];

                    $level = (int) $buff2->approval_level;
                    $status = $approvalStatuses[$level] ?? ['label' => 'Unknown', 'class' => 'bg-secondary'];
                    
                    $data[] = '<div class="tt-item">
                        <div class="tt-icon ' . $status['class'] . '">
                            <span class="tt-dot"></span>
                        </div>
                        <div class="tt-content">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="fw-semibold">' . date("d M Y H:i:s", strtotime($buff2->updated_at)) . '</div>
                                <span class="badge rounded-pill ' . $status['class'] . ' text-white">' . $status['label'] . '</span>
                            </div>
                            <div class="small mt-1">' . $status['label'] . ' by <span class="fw-semibold">' . $buff2->approver_name . '</span></div>
                        </div>
                    </div>';
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => implode("", $data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching badge info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching approval timeline'
            ], 404);
        }
    }

    public function viewPdf($id)
    {
        // $pdf = Pdf::loadView('pages.submission.pdf', [
        //     'title' => 'Budget'
        // ]);

        // return $pdf->download('budget-proposal.pdf');

        $data = [
            // ambil data sesuai kebutuhan
        ];
        $pdf = Pdf::loadView('pages.submission.pdf', $data)
                  ->setPaper('a4', 'portrait');

        // STREAM = preview di browser
        return $pdf->stream('budget-proposal-preview.pdf');
        // return view('pages.submission.pdf');         
    }

    /**
     * Get approval status for a transaction
     */
    public function getApprovalStatus($id)
    {
        try {
            $result = $this->approvalTransactionService->getApprovalStatus($id);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error fetching approval status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching approval status'
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

            if (!$employment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employment data not found',
                    'data' => [],
                    'count' => 0
                ]);
            }

            $result = $this->approvalTransactionService->getPendingApprovalsForUser($employment->id);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error fetching pending approvals: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching pending approvals',
                'data' => [],
                'count' => 0
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

            if (!$employment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employment data not found'
                ], 404);
            }

            $year = $request->input('year');
            
            // Use service to get counts
            $result = $this->approvalTransactionService->getApprovalCounts(
                $employment->id,
                ['year' => $year]
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error fetching approval counts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching approval counts'
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

            if (!$employment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employment data not found'
                ], 404);
            }

            $status = $request->input('status'); // pending, approved, rejected
            $year = $request->input('year');
            $search = $request->input('search');
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);

            // Use service to get approval items
            $result = $this->approvalTransactionService->getApprovalItemsByStatus(
                $employment->id,
                $status,
                [
                    'year' => $year,
                    'search' => $search,
                    'page' => $page,
                    'per_page' => $perPage
                ]
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error fetching approval data: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching approval data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel approval request for a transaction
     */
    public function cancelApproval($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            // Check if user owns this transaction
            if ($transaction->user_id != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $result = $this->approvalTransactionService->cancelApproval($id);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error cancelling approval: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resubmit transaction for approval
     */
    public function resubmitForApproval($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            // Check if user owns this transaction
            if ($transaction->user_id != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Check if transaction can be resubmitted (status is pending/cancelled)
            if (!in_array($transaction->status, [Transaction::STATUS_PENDING, Transaction::STATUS_CANCELLED])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction cannot be resubmitted in current status'
                ], 400);
            }

            $result = $this->approvalTransactionService->submitForApproval($id);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error resubmitting for approval: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error resubmitting for approval: ' . $e->getMessage()
            ], 500);
        }
    }

}
