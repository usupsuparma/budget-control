<?php

namespace App\Http\Controllers;

use App\Models\BudgetSubmission;
use App\Models\Division;
use App\Models\KPIWorkPlan;
use App\Models\BudgetCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BudgetSubmissionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $budgetSubmissions = BudgetSubmission::with(['user', 'division', 'workPlan', 'budgetAccount'])
            ->orderBy('submission_date', 'desc')
            ->paginate(15);

        $divisions = Division::get();
        $workPlans = KPIWorkPlan::orderBy('year', 'desc')
            ->orderBy('activity')
            ->get();
        $budgetAccounts = BudgetCode::orderBy('stock_code')->limit(10)->get();
        return view('pages.budget.budget-submission', compact(
            'budgetSubmissions',
            'divisions',
            'workPlans',
            'budgetAccounts',
            'user'
        ));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'division_id' => 'required|exists:division,id',
                'submission_date' => 'required|date',
                'type' => 'required|in:add,relocation',
                'work_plan_id' => 'required|exists:kpi_workplans,id',
                'budget_account_id' => 'required',
                'estimation_amount' => 'required|numeric|min:0',
                'description' => 'nullable|string',
            ]);

            $user = Auth::user();
            if (!$user) {
                throw new \Exception('User not authenticated. Please login again.');
            }

            $division = Division::find($validated['division_id']);
            if (!$division) {
                throw new \Exception('Division not found. Please select a valid division.');
            }

            BudgetSubmission::create([
                'user_id' => $user->id,
                'division_id' => $validated['division_id'],
                'division_name' => $division->name,
                'work_plan_id' => $validated['work_plan_id'],
                'submission_date' => $validated['submission_date'],
                'type' => $validated['type'],
                'budget_account_id' => $validated['budget_account_id'],
                'estimation_amount' => $validated['estimation_amount'],
                'description' => $validated['description'],
                'status' => 0, // Pending
            ]);

            DB::commit();
            return redirect()->route('budget.submission.index')
                ->with('success', 'Budget submission created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e);
            DB::rollback();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Validation failed. Please check the form and try again.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create budget submission: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $budgetSubmission = BudgetSubmission::findOrFail($id);
            
            // Check if user can edit (only pending submissions)
            if ($budgetSubmission->status != 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending submissions can be edited. This submission has already been ' . 
                                ($budgetSubmission->status == 1 ? 'approved' : 'rejected') . '.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $budgetSubmission
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Budget submission not found. It may have been deleted.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load submission data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $budgetSubmission = BudgetSubmission::findOrFail($id);

            // Check if user can edit
            if ($budgetSubmission->status != 0) {
                throw new \Exception('Only pending submissions can be edited. This submission has already been ' . 
                                    ($budgetSubmission->status == 1 ? 'approved' : 'rejected') . '.');
            }

            $validated = $request->validate([
                'division_id' => 'required|exists:division,id',
                'submission_date' => 'required|date',
                'type' => 'required|in:add,relocation',
                'work_plan_id' => 'required|exists:kpi_workplans,id',
                'budget_account_id' => 'required|exists:budget_codes,id',
                'estimation_amount' => 'required|numeric|min:0',
                'description' => 'nullable|string',
            ]);

            $division = Division::find($validated['division_id']);
            if (!$division) {
                throw new \Exception('Division not found. Please select a valid division.');
            }

            $budgetSubmission->update([
                'division_id' => $validated['division_id'],
                'division_name' => $division->name,
                'work_plan_id' => $validated['work_plan_id'],
                'submission_date' => $validated['submission_date'],
                'type' => $validated['type'],
                'budget_account_id' => $validated['budget_account_id'],
                'estimation_amount' => $validated['estimation_amount'],
                'description' => $validated['description'],
            ]);

            DB::commit();
            return redirect()->route('budget.submission.index')
                ->with('success', 'Budget submission updated successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollback();
            return redirect()->route('budget.submission.index')
                ->with('error', 'Budget submission not found. It may have been deleted.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Validation failed. Please check the form and try again.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('budget.submission.index')
                ->with('error', 'Failed to update budget submission: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $budgetSubmission = BudgetSubmission::findOrFail($id);

            // Check if user can delete (only pending submissions)
            if ($budgetSubmission->status != 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending submissions can be deleted. This submission has already been ' . 
                                ($budgetSubmission->status == 1 ? 'approved' : 'rejected') . '.'
                ], 403);
            }

            $budgetSubmission->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Budget submission deleted successfully.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Budget submission not found. It may have been already deleted.'
            ], 404);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete budget submission: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $budgetSubmission = BudgetSubmission::findOrFail($id);

            if ($budgetSubmission->status != 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending submissions can be approved. This submission has already been ' . 
                                ($budgetSubmission->status == 1 ? 'approved' : 'rejected') . '.'
                ], 403);
            }

            $budgetSubmission->update(['status' => 1]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Budget submission approved successfully.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Budget submission not found. It may have been deleted.'
            ], 404);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve budget submission: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject($id)
    {
        DB::beginTransaction();
        try {
            $budgetSubmission = BudgetSubmission::findOrFail($id);

            if ($budgetSubmission->status != 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending submissions can be rejected. This submission has already been ' . 
                                ($budgetSubmission->status == 1 ? 'approved' : 'rejected') . '.'
                ], 403);
            }

            $budgetSubmission->update(['status' => 2]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Budget submission rejected successfully.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Budget submission not found. It may have been deleted.'
            ], 404);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject budget submission: ' . $e->getMessage()
            ], 500);
        }
    }
}
