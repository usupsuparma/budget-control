<?php

namespace App\Http\Controllers;

use App\Models\BudgetSubmission;
use App\Models\Division;
use App\Models\BudgetCode;
use App\Models\KPIWorkPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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
        // budgetCodes will be loaded via AJAX
        return view('pages.budget.budget-submission', compact(
            'budgetSubmissions',
            'divisions',
            'workPlans',
            'user'
        ));
    }

    /**
     * Get table data via AJAX (for refreshing without page reload)
     */
    public function getData(Request $request)
    {
        try {
            $budgetSubmissions = BudgetSubmission::with(['user', 'division', 'workPlan', 'budgetAccount'])
                ->orderBy('submission_date', 'desc')
                ->get();

            $html = '';
            $no = 1;

            foreach ($budgetSubmissions as $submission) {
                $statusColor = match($submission->status) {
                    0 => 'warning',
                    1 => 'success',
                    2 => 'danger',
                    default => 'secondary'
                };
                
                $statusLabel = match($submission->status) {
                    0 => 'Pending',
                    1 => 'Approved',
                    2 => 'Rejected',
                    default => 'Unknown'
                };
                
                $typeColor = $submission->type == 'add' ? 'info' : 'secondary';
                $typeLabel = $submission->type == 'add' ? 'Add Budget' : 'Relocation';
                
                $html .= '<tr>';
                $html .= '<td>' . $no++ . '</td>';
                $html .= '<td>' . e($submission->submission_date->format('d/m/Y')) . '</td>';
                $html .= '<td>' . e($submission->division->name ?? '-') . '</td>';
                $html .= '<td><span class="badge bg-' . $typeColor . '">' . $typeLabel . '</span></td>';
                $html .= '<td><small>' . e($submission->workPlan->activity ?? '-') . '</small></td>';
                $html .= '<td><small>' . e(\Illuminate\Support\Str::limit($submission->description ?? '', 50)) . '</small></td>';
                $html .= '<td class="text-end">Rp ' . number_format($submission->estimation_amount, 0, ',', '.') . '</td>';
                $html .= '<td><small>' . e($submission->budgetAccount->stock_code ?? '-') . ' | ' . e($submission->budgetAccount->name ?? '-') . '</small></td>';
                $html .= '<td><span class="badge bg-' . $statusColor . '">' . $statusLabel . '</span></td>';
                
                // Action buttons
                $html .= '<td><div class="btn-group" role="group">';
                
                if ($submission->status == 0) { // Pending
                    $html .= '<button type="button" class="btn btn-sm btn-warning" onclick="editSubmission(' . $submission->id . ')" title="Edit">';
                    $html .= '<i class="ri-edit-line"></i></button>';
                    $html .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteSubmission(' . $submission->id . ')" title="Delete">';
                    $html .= '<i class="ri-delete-bin-line"></i></button>';
                    $html .= '<button type="button" class="btn btn-sm btn-success" onclick="approveSubmission(' . $submission->id . ')" title="Approve">';
                    $html .= '<i class="ri-check-line"></i></button>';
                } else {
                    $html .= '<button type="button" class="btn btn-sm btn-secondary" disabled>';
                    $html .= '<i class="ri-eye-line"></i></button>';
                }
                
                $html .= '</div></td>';
                $html .= '</tr>';
            }

            // No data row is intentionally omitted — DataTables shows its own emptyTable message.

            return response()->json([
                'success' => true,
                'html' => $html,
                'total' => $budgetSubmissions->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load table data: ' . $e->getMessage()
            ], 500);
        }
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
            
            // Check if AJAX request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Budget submission created successfully.'
                ]);
            }
            
            return redirect()->route('budget.submission.index')
                ->with('success', 'Budget submission created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            
            // Check if AJAX request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed. Please check the form and try again.',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Validation failed. Please check the form and try again.');
        } catch (\Exception $e) {
            DB::rollback();
            
            // Check if AJAX request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create budget submission: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create budget submission: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $budgetSubmission = BudgetSubmission::with('budgetAccount')->findOrFail($id);
            
            // Check if user can edit (only pending submissions)
            if ($budgetSubmission->status != 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending submissions can be edited. This submission has already been ' . 
                                ($budgetSubmission->status == 1 ? 'approved' : 'rejected') . '.'
                ], 403);
            }

            $budgetAccountText = null;
            if ($budgetSubmission->budgetAccount) {
                $budgetAccountText = $budgetSubmission->budgetAccount->stock_code . ' - ' . $budgetSubmission->budgetAccount->name;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $budgetSubmission->id,
                    'division_id' => $budgetSubmission->division_id,
                    'submission_date' => $budgetSubmission->submission_date->format('Y-m-d'),
                    'type' => $budgetSubmission->type,
                    'work_plan_id' => $budgetSubmission->work_plan_id,
                    'budget_account_id' => $budgetSubmission->budget_account_id,
                    'budget_account_text' => $budgetAccountText,
                    'estimation_amount' => $budgetSubmission->estimation_amount,
                    'description' => $budgetSubmission->description,
                    'status' => $budgetSubmission->status,
                ]
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
                'budget_account_id' => 'required',
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
            
            // Check if AJAX request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Budget submission updated successfully.'
                ]);
            }
            
            return redirect()->route('budget.submission.index')
                ->with('success', 'Budget submission updated successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollback();
            
            // Check if AJAX request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Budget submission not found. It may have been deleted.'
                ], 404);
            }
            
            return redirect()->route('budget.submission.index')
                ->with('error', 'Budget submission not found. It may have been deleted.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            
            // Check if AJAX request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed. Please check the form and try again.',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Validation failed. Please check the form and try again.');
        } catch (\Exception $e) {
            DB::rollback();
            
            // Check if AJAX request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update budget submission: ' . $e->getMessage()
                ], 500);
            }
            
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

    /**
     * Get all budget codes for dropdown (simple AJAX)
     */
    public function getAllBudgetCodes()
    {
        $budgetCodes = Cache::remember('budget_codes_all', 3600, function() {
            return BudgetCode::select('id', 'stock_code', 'name')
                ->orderBy('stock_code')
                ->get()
                ->map(function($code) {
                    return [
                        'value' => $code->id,
                        'label' => $code->stock_code . ' - ' . $code->name
                    ];
                });
        });

        return response()->json($budgetCodes);
    }

    /**
     * Get budget codes with pagination and search for AJAX requests
     */
    public function getBudgetCodes(Request $request)
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $id = $request->get('id', null);
        $perPage = 30; // Load 30 items per request

        // If requesting specific ID (for edit mode)
        if ($id) {
            $budgetCode = BudgetCode::find($id);
            if ($budgetCode) {
                return response()->json([
                    'results' => [[
                        'id' => $budgetCode->id,
                        'text' => $budgetCode->stock_code . ' - ' . $budgetCode->name
                    ]]
                ]);
            }
            return response()->json(['results' => []]);
        }

        $query = BudgetCode::query();

        // If there's a search term, filter by stock_code or name
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('stock_code', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        // Get total count for pagination
        $total = $query->count();

        // Get paginated results
        $budgetCodes = $query->select('id', 'stock_code', 'name')
            ->orderBy('stock_code')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Format for Select2
        $results = $budgetCodes->map(function($code) {
            return [
                'id' => $code->id,
                'text' => $code->stock_code . ' - ' . $code->name
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }
}
