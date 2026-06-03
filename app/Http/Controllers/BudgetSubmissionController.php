<?php

namespace App\Http\Controllers;

use App\Models\BudgetCode;
use App\Services\BudgetSubmissionService\BudgetSubmissionService;
use App\Services\BudgetSubmissionService\DTOs\BudgetSubmissionData;
use App\Http\Requests\StoreBudgetSubmissionRequest;
use App\Http\Requests\UpdateBudgetSubmissionRequest;
use App\Exceptions\DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class BudgetSubmissionController extends Controller
{
    public function __construct(
        private readonly BudgetSubmissionService $budgetSubmissionService
    ) {}

    public function index()
    {
        $user = Auth::user();
        $data = $this->budgetSubmissionService->getIndexData($user);

        return view('pages.budget.budget-submission', $data);
    }

    /**
     * Get table data via AJAX (for refreshing without page reload)
     */
    public function getData(Request $request)
    {
        try {
            $user = Auth::user();
            $budgetSubmissions = $this->budgetSubmissionService->getAjaxData($user);

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

    public function store(StoreBudgetSubmissionRequest $request)
    {
        try {
            $user = Auth::user();
            $data = BudgetSubmissionData::fromArray($request->validated());
            
            $this->budgetSubmissionService->store($data, $user);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Budget submission created successfully.'
                ]);
            }
            
            return redirect()->route('budget.submission.index')
                ->with('success', 'Budget submission created successfully.');
        } catch (DomainException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
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
            $data = $this->budgetSubmissionService->edit($id);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
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

    public function update(UpdateBudgetSubmissionRequest $request, $id)
    {
        try {
            $data = BudgetSubmissionData::fromArray($request->validated());
            
            $this->budgetSubmissionService->update($id, $data);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Budget submission updated successfully.'
                ]);
            }
            
            return redirect()->route('budget.submission.index')
                ->with('success', 'Budget submission updated successfully.');
        } catch (DomainException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Budget submission not found. It may have been deleted.'
                ], 404);
            }
            return redirect()->route('budget.submission.index')
                ->with('error', 'Budget submission not found. It may have been deleted.');
        } catch (\Exception $e) {
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
        try {
            $this->budgetSubmissionService->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Budget submission deleted successfully.'
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Budget submission not found. It may have been already deleted.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete budget submission: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approve($id)
    {
        try {
            $this->budgetSubmissionService->approve($id);

            return response()->json([
                'success' => true,
                'message' => 'Budget submission approved successfully.'
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Budget submission not found. It may have been deleted.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve budget submission: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject($id)
    {
        try {
            $this->budgetSubmissionService->reject($id);

            return response()->json([
                'success' => true,
                'message' => 'Budget submission rejected successfully.'
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Budget submission not found. It may have been deleted.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject budget submission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get workplans filtered by division and current year
     */
    public function getWorkPlansByDivision(Request $request)
    {
        $divisionId = $request->get('division_id');
        $year = date('Y');

        if (!$divisionId) {
            return response()->json([]);
        }

        $workPlans = \App\Models\KPIWorkPlan::whereDivisionIn([$divisionId])
            ->where('year', $year)
            ->where('status', 'approved')
            ->select('id', 'activity', 'year')
            ->orderBy('activity')
            ->get()
            ->map(function($wp) {
                return [
                    'value' => $wp->id,
                    'label' => '[' . $wp->year . '] ' . $wp->activity
                ];
            });

        return response()->json($workPlans);
    }

    /**
     * Get all budget codes for dropdown (simple AJAX)
     */
    public function getAllBudgetCodes(Request $request)
    {
        $query = trim((string) $request->get('q', ''));
        $page = max(1, (int) $request->get('page', 1));
        $limit = min(100, max(1, (int) $request->get('limit', 20)));
        $selectedId = $request->get('id');

        $codeColumn = Schema::hasColumn('budget_code', 'budget_code') ? 'budget_code' : 'stock_code';

        // Optional single fetch for edit mode
        if (!empty($selectedId)) {
            $selected = BudgetCode::select('id', $codeColumn, 'name')
                ->where('id', $selectedId)
                ->first();

            if (! $selected) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'has_more' => false,
                    'page' => 1,
                    'total' => 0,
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [[
                    'value' => (string) $selected->id,
                    'label' => $selected->{$codeColumn} . ' - ' . $selected->name
                ]],
                'has_more' => false,
                'page' => 1,
                'total' => 1,
            ]);
        }

        $queryBuilder = BudgetCode::query()
            ->select('id', $codeColumn, 'name');

        if (!empty($query)) {
            $queryBuilder->where(function($builder) use ($query, $codeColumn) {
                $builder->where($codeColumn, 'like', '%' . $query . '%')
                    ->orWhere('name', 'like', '%' . $query . '%');
            });
        }

        $queryBuilder->orderBy($codeColumn);

        $total = $queryBuilder->count();
        $offset = ($page - 1) * $limit;
        $budgetCodes = $queryBuilder->skip($offset)->take($limit)->get()
            ->map(function($code) use ($codeColumn) {
                $codeValue = $code->{$codeColumn};
                return [
                    'value' => (string) $code->id,
                    'label' => $codeValue . ' - ' . $code->name
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $budgetCodes,
            'has_more' => (($offset + $limit) < $total),
            'page' => $page,
            'total' => $total,
            'query' => $query,
            'limit' => $limit,
        ]);
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
