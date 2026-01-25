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
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class SubmissionController extends Controller
{
    protected $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    public function user()
    {
        $title = 'Submission Users';

        // Get summary data
        $userId = Auth::user()->employee_id;
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
            $query = Transaction::query();
            $query->where('user_id', $userId);
            $query->with(['details', 'approvals' => function($q) use ($userId) {
                $q->where('approver_id', $userId)
                  ->where('status', 0); // pending
            }]);

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
            $transactions->getCollection()->transform(function($transaction) use ($userId) {
                $transaction->can_approve = $transaction->approvals->isNotEmpty();
                $transaction->pending_approval = $transaction->approvals->first();
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
                'status' => 0, // Submission
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

            $maxLevel = null;
            if ($estimatedAmount > 50000000) {
                $maxLevel = 1;
            } elseif ($estimatedAmount > 5000000 && $estimatedAmount <= 50000000) {
                $maxLevel = 2;
            } elseif ($estimatedAmount <= 5000000) {
                $maxLevel = 3;
            }

            $uplines_top_down = session('uplines_top_down');
            $utd = collect($uplines_top_down)
                    ->where('level', '>=', $maxLevel)
                    ->sortByDesc('level')
                    ->values()
                    ->toArray();

            $threshold_level = array("","50000000","50000000","5000000");
            
            $i=0;
            foreach($utd as $buff){
                TransactionApproval::create([
                    'transaction_id' => $transaction->id, 
                    'approver_id' => $buff['id'], 
                    'approver_name' => $buff['fname']." ".$buff['lname'], 
                    'approval_level' => $buff['level'], 
                    'threshold_id' => $threshold_level[(int) $buff['level']], 
                    'is_required' => 1, 
                    'status' => 0, 
                    // 'approved_at' => 0, 
                    'comments' => 0, 
                    'sequence_order' => $i, 
                    // 'notified_at' => date("Y-m-d H:i:s"), 
                    'reminder_count' => 1, 
                    'reminder_last_sent' => date("Y-m-d H:i:s"), 
                    'approval_method' => 0, 
                    'ip_address' => 0
                ]);
                $i++;
            }

            $budgetcontrol_level = array(
                [
                    "id" => 190,
                    "fname" => "Ryan",
                    "lname" => "Candra Purnama",
                    "type" => "budget_control_staff",
                    "level" => "-1",
                    "threshold_level" => 0
                ],
                [
                    "id" => 181,
                    "fname" => "A Dadan",
                    "lname" => "Hadiana",
                    "type" => "finance_division",
                    "level" => "-2",
                    "threshold_level" => 50000000
                ],
                [
                    "id" => 39,
                    "fname" => "Yara",
                    "lname" => "Budhi Widowati",
                    "type" => "finance_director",
                    "level" => "-3",
                    "threshold_level" => 50000000
                ],
                [
                    "id" => 42,
                    "fname" => "Yasuhiko",
                    "lname" => "Takaizumi",
                    "type" => "president_director",
                    "level" => "-4",
                    "threshold_level" => 50000000
                ],
            );

            $maxApproverTypes = [];

            if ($estimatedAmount < 50000000) {
                // sampai finance_division (A Dadan)
                $maxApproverTypes = [
                    'budget_control_staff',
                    'finance_division',
                ];
            } else {
                // sampai finance_director & president_director
                $maxApproverTypes = [
                    'budget_control_staff',
                    'finance_division',
                    'finance_director',
                    'president_director',
                ];
            }

            foreach($budgetcontrol_level as $buff){
                // skip jika approver tidak termasuk flow
                if (!in_array($buff['type'], $maxApproverTypes, true)) {
                    continue;
                }
                TransactionApproval::create([
                    'transaction_id' => $transaction->id, 
                    'approver_id' => $buff['id'], 
                    'approver_name' => $buff['fname']." ".$buff['lname'], 
                    'approval_level' => $buff['level'], 
                    'threshold_id' => $buff['threshold_level'], 
                    'is_required' => 1, 
                    'status' => 0, 
                    // 'approved_at' => 0, 
                    'comments' => 0, 
                    'sequence_order' => $i, 
                    // 'notified_at' => date("Y-m-d H:i:s"), 
                    'reminder_count' => 1, 
                    'reminder_last_sent' => date("Y-m-d H:i:s"), 
                    'approval_method' => 0, 
                    'ip_address' => 0
                ]);
                $i++;
            }

            $transaction = Transaction::findOrFail($transaction->id);
            $transaction->update([
                'current_approval_level' => $utd[0]['level'],
                'required_approval_levels' => count($utd)+count($maxApproverTypes)
            ]);

            DB::commit();

            // Create approval chain after transaction is committed
            $approvalResult = $this->approvalService->createApprovalChain($transaction->id);
            
            if (!$approvalResult['success']) {
                Log::warning('Failed to create approval chain: ' . $approvalResult['message']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => $transaction->load(['details', 'approvals'])
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
                'jobLevel',
                'jobPosition',
                'unit'
            ])->findOrFail($id);

            // Check if user owns this transaction
            if ($transaction->user_id != Auth::id()) {
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

    public function admin()
    {
        $title = 'Submission Admin';
        $userId = Auth::user()->employee_id;

        // Get summary data
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

        return view('pages.submission.admin', compact(
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
     * Approve transaction
     */
    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comments' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $userId = Auth::id();
            $userName = Auth::user()->first_name . ' ' . Auth::user()->last_name;

            // Find pending approval for this user
            $approval = \App\Models\TransactionApproval::where('transaction_id', $id)
                ->where('approver_id', $userId)
                ->where('status', 0) // pending
                ->first();

            if (!$approval) {
                return response()->json([
                    'success' => false,
                    'message' => 'Approval not found or already processed'
                ], 404);
            }

            // Process approval using ApprovalService
            $result = $this->approvalService->processApproval(
                $approval->id,
                1, // status approved
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
     * Reject transaction
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comments' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $userId = Auth::id();
            $userName = Auth::user()->name;

            // Find pending approval for this user
            $approval = \App\Models\TransactionApproval::where('transaction_id', $id)
                ->where('approver_id', $userId)
                ->where('status', 0) // pending
                ->first();

            if (!$approval) {
                return response()->json([
                    'success' => false,
                    'message' => 'Approval not found or already processed'
                ], 404);
            }

            // Process rejection using ApprovalService
            $result = $this->approvalService->processApproval(
                $approval->id,
                2, // status rejected
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
            $transaction = Transaction::where('id', $id)->get();
            $transactionApproval = TransactionApproval::where('transaction_id', $id)->orderBy('sequence_order', 'asc')->get();

            $data = array();
            foreach($transaction as $buff){
                $data[] = '<div class="tt-item">
                    <div class="tt-icon bg-warning">
                    <!-- icon (optional) -->
                    <span class="tt-dot"></span>
                    </div>

                    <div class="tt-content">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="fw-semibold">'.date("d M Y H:i:s",strtotime($buff->created_at)).'</div>
                        <span class="badge rounded-pill bg-warning text-dark">Submission</span>
                    </div>
                    <div class="small mt-1">Submission by <span class="fw-semibold">'.$buff->user_name.'</span></div>
                    </div>
                </div>';
            }

            foreach($transactionApproval as $buff2){
                if($buff2->status==0){
                    $approvalStatuses = [
                        3  => ['label' => 'Department',          'class' => 'bg-info'],
                        2  => ['label' => 'Division',            'class' => 'bg-info'],
                        1  => ['label' => 'Director',            'class' => 'bg-info'],
                        -1  => ['label' => 'Budget Control',     'class' => 'bg-info'],
                        -2  => ['label' => 'Finance Division',   'class' => 'bg-info'],
                        -3  => ['label' => 'Finance Director',   'class' => 'bg-info'],
                        -4  => ['label' => 'President Director', 'class' => 'bg-success'],
                    ];

                    $level = (int) $buff2->approval_level;

                    $status = $approvalStatuses[$level] ?? [
                        'label' => 'Unknown',
                        'class' => 'bg-secondary'
                    ];

                    $data[] = '<div class="tt-item">
                        <div class="tt-icon bg-light">
                        <!-- icon (optional) -->
                        <span class="tt-dot"></span>
                        </div>

                        <div class="tt-content">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="fw-semibold"></div>
                            <span class="badge rounded-pill bg-light text-muted">Pending</span>
                        </div>
                        <div class="small mt-1 text-muted">'.$status['label'].' by <span class="fw-semibold">'.$buff2->approver_name.'</span></div>
                        </div>
                    </div>';
                } else {
                    $approvalStatuses = [
                        3  => ['label' => 'Approved Department',          'class' => 'bg-info'],
                        2  => ['label' => 'Approved Division',            'class' => 'bg-info'],
                        1  => ['label' => 'Approved Director',            'class' => 'bg-info'],
                        -1  => ['label' => 'Approved Budget Control',     'class' => 'bg-info'],
                        -2  => ['label' => 'Approved Finance Division',   'class' => 'bg-info'],
                        -3  => ['label' => 'Approved Finance Director',   'class' => 'bg-info'],
                        -4  => ['label' => 'Approved President Director', 'class' => 'bg-success'],
                    ];

                    $level = (int) $buff2->approval_level;

                    // fallback kalau level tidak dikenal
                    $status = $approvalStatuses[$level] ?? [
                        'label' => 'Unknown',
                        'class' => 'bg-secondary'
                    ];
                    $data[] = '<div class="tt-item">
                        <div class="tt-icon '.$status['class'].'">
                        <!-- icon (optional) -->
                        <span class="tt-dot"></span>
                        </div>

                        <div class="tt-content">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="fw-semibold">'.date("d M Y H:i:s",strtotime($buff2->updated_at)).'</div>
                            <span class="badge rounded-pill '.$status['class'].' text-white">'.$status['label'].'</span>
                        </div>
                        <div class="small mt-1">'.$status['label'].' by <span class="fw-semibold">'.$buff2->approver_name.'</span></div>
                        </div>
                    </div>';
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => implode("",$data)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Budget not found'
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

}
