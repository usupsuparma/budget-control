<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\JobPosition;
use App\Models\KPIWorkPlan;
use App\Models\BudgetCode;
use App\Models\Unit;
use App\Models\WorkplanBudgetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubmissionController extends Controller
{
    public function user()
    {
        $title = 'Submission Users';

        // Get summary data
        $userId = Auth::id();
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
            'units'
        ));
    }

    public function getData(Request $request)
    {
        try {

            $query = Transaction::query();
            $query->with(['details', 'historyApprovals']);

            // Filter by year
            if ($request->has('year') && $request->year != '' && $request->year != 'all') {
                $query->whereYear('transaction_date', $request->year);
            }

            // Filter by status
            if ($request->has('status') && $request->status !== '' && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Pagination
            $perPage = $request->per_page ?? 10;
            $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);

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
                'unit_name' => $unit->name ?? '',
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

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => $transaction->load('details')
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
            $transaction = Transaction::with('details')->findOrFail($id);

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
        return view('pages.submission.admin', compact('title'));
    }
}
