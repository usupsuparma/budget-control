<?php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\BudgetCode;
use App\Models\KPIWorkPlan;
use App\Models\KPIDivision;
use App\Models\Division;
use App\Models\WorkplanBudgetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class BudgetUserController extends Controller
{
    /**
     * Display budget user page
     */
    public function index(Request $request)
    {
        // Get unique divisions from KPI Division
        $kpiDivisions = KPIDivision::with('division')
            ->select('division_id')
            ->distinct()
            ->get();
        
        $divisions = $kpiDivisions->map(function($kpi) {
            return $kpi->division;
        })->filter()->unique('id')->values();
        
        $years = range(date('Y'), date('Y') - 5);
        return view('pages.budget.budget-user', compact('years', 'divisions'));
    }

    /**
     * Get divisions for dropdown (AJAX)
     */
    public function getDivisions(Request $request)
    {
        try {
            $kpiDivisions = KPIDivision::with('division')
                ->select('division_id')
                ->distinct()
                ->get();
            
            $divisions = $kpiDivisions->map(function($kpi) {
                return $kpi->division;
            })->filter()->unique('id')->values();

            return response()->json([
                'success' => true,
                'data' => $divisions
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading divisions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load divisions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get workplans for current user filtered by division and year
     */
    public function getWorkplans(Request $request)
    {
        try {
            $divisionId = $request->input('division_id');
            $year = $request->input('year');

            if (!$divisionId || !$year) {
                return response()->json([
                    'success' => false,
                    'message' => 'Division and Year are required'
                ], 400);
            }

            // Get workplans based on division and year
            $workplans = KPIWorkPlan::with([
                    'kpiDepartment' => function($query) {
                        $query->with(['department', 'kpiDivision']);
                    },
                    'kpiSection' => function($query) {
                        $query->with(['section', 'kpiDepartment.kpiDivision']);
                    }
                ])
                ->where('year', $year)
                ->where(function($query) use ($divisionId) {
                    // For department workplans (kpi_type = 'department')
                    $query->where(function($q) use ($divisionId) {
                        $q->where('kpi_type', 'department')
                          ->whereHas('kpiDepartment', function($dept) use ($divisionId) {
                              $dept->whereHas('kpiDivision', function($div) use ($divisionId) {
                                  $div->where('division_id', $divisionId);
                              });
                          });
                    })
                    // For section workplans (kpi_type = 'section')
                    ->orWhere(function($q) use ($divisionId) {
                        $q->where('kpi_type', 'section')
                          ->whereHas('kpiSection', function($sect) use ($divisionId) {
                              $sect->whereHas('kpiDepartment', function($dept) use ($divisionId) {
                                  $dept->whereHas('kpiDivision', function($div) use ($divisionId) {
                                      $div->where('division_id', $divisionId);
                                  });
                              });
                          });
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $workplans
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading workplans: ' . $e->getMessage(), ['BudgetUserController', 'getWorkplans']);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load workplans: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get budget categories (parent level 1 and children level 2)
     */
    public function getCategories(Request $request, $workplanId)
    {
        try {
            $workplan = KPIWorkPlan::findOrFail($workplanId);
            
            // Get parent categories (level 1) with their children (level 2)
            $categories = BudgetCategory::with(['children' => function($query) {
                $query->where('level', 2)
                      ->where('is_active', true)
                      ->orderBy('sort_order');
            }])
            ->where('level', 1)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $categories,
                'workplan' => $workplan
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get budget items by category
     */
    public function getItems(Request $request, $workplanId)
    {
        try {
            $categoryId = $request->input('category_id');
            
            $items = WorkplanBudgetItem::with(['category', 'budgetCodeRelation', 'approver'])
                ->where('kpi_workplan_id', $workplanId)
                ->where('budget_category_id', $categoryId)
                ->orderBy('sort_order')
                ->get();

            // Get available budget codes - select only stock_code and inchargeCode
            $budgetCodes = BudgetCode::active()
                ->select('id', 'stock_code', 'name', 'inchargeCode')
                ->orderBy('stock_code')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $items,
                'budgetCodes' => $budgetCodes
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading items: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new budget item
     */
    public function store(Request $request, $workplanId)
    {
        try {
            $validated = $request->validate([
                'budget_category_id' => 'required|exists:budget_categories,id',
                'category_type' => 'required|in:Routine,Carry Over,Turn Around,Multi Year',
                'description' => 'required|string',
                'stock_code' => 'nullable|string|max:50',
                'budget_code' => 'nullable|string|max:50',
                'product_line' => 'nullable|string|max:100',
                'cost_center' => 'nullable|string|max:50',
                'beg_balance' => 'nullable|string|max:100',
                'cons_rate' => 'nullable|string|max:100',
                'unit' => 'nullable|string|max:50',
                'total' => 'nullable|numeric',
                'price_estimation' => 'nullable|numeric',
                'price_estimation_description' => 'nullable|string|max:255',
                'activity_jan' => 'nullable|integer|min:0|max:1000',
                'activity_feb' => 'nullable|integer|min:0|max:1000',
                'activity_mar' => 'nullable|integer|min:0|max:1000',
                'activity_apr' => 'nullable|integer|min:0|max:1000',
                'activity_may' => 'nullable|integer|min:0|max:1000',
                'activity_jun' => 'nullable|integer|min:0|max:1000',
                'activity_jul' => 'nullable|integer|min:0|max:1000',
                'activity_aug' => 'nullable|integer|min:0|max:1000',
                'activity_sep' => 'nullable|integer|min:0|max:1000',
                'activity_oct' => 'nullable|integer|min:0|max:1000',
                'activity_nov' => 'nullable|integer|min:0|max:1000',
                'activity_dec' => 'nullable|integer|min:0|max:1000',
                'notes' => 'nullable|string',
            ]);

            $validated['kpi_workplan_id'] = $workplanId;
            $validated['status'] = 'draft';
            
            // Set sort order
            $maxOrder = WorkplanBudgetItem::where('kpi_workplan_id', $workplanId)
                ->where('budget_category_id', $validated['budget_category_id'])
                ->max('sort_order');
            $validated['sort_order'] = ($maxOrder ?? 0) + 1;

            $item = WorkplanBudgetItem::create($validated);
            $item->load(['category', 'budgetCodeRelation']);

            // Update parent workplan budget
            $workplan = KPIWorkPlan::find($workplanId);
            if ($workplan) {
                $workplan->updateBudgetTotal();
            }

            return response()->json([
                'success' => true,
                'message' => 'Budget item created successfully',
                'data' => $item
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update budget item
     */
    public function update(Request $request, $workplanId, $itemId)
    {
        try {
            $item = WorkplanBudgetItem::where('kpi_workplan_id', $workplanId)
                ->findOrFail($itemId);

            // Check if item can be edited
            if (!$item->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This item cannot be edited because it has been approved'
                ], 403);
            }

            $validated = $request->validate([
                'budget_category_id' => 'required|exists:budget_categories,id',
                'category_type' => 'required|in:Routine,Carry Over,Turn Around,Multi Year',
                'description' => 'required|string',
                'stock_code' => 'nullable|string|max:50',
                'budget_code' => 'nullable|string|max:50',
                'product_line' => 'nullable|string|max:100',
                'cost_center' => 'nullable|string|max:50',
                'beg_balance' => 'nullable|string|max:100',
                'cons_rate' => 'nullable|string|max:100',
                'unit' => 'nullable|string|max:50',
                'total' => 'nullable|numeric',
                'price_estimation' => 'nullable|numeric',
                'price_estimation_description' => 'nullable|string|max:255',
                'activity_jan' => 'nullable|integer|min:0|max:1000',
                'activity_feb' => 'nullable|integer|min:0|max:1000',
                'activity_mar' => 'nullable|integer|min:0|max:1000',
                'activity_apr' => 'nullable|integer|min:0|max:1000',
                'activity_may' => 'nullable|integer|min:0|max:1000',
                'activity_jun' => 'nullable|integer|min:0|max:1000',
                'activity_jul' => 'nullable|integer|min:0|max:1000',
                'activity_aug' => 'nullable|integer|min:0|max:1000',
                'activity_sep' => 'nullable|integer|min:0|max:1000',
                'activity_oct' => 'nullable|integer|min:0|max:1000',
                'activity_nov' => 'nullable|integer|min:0|max:1000',
                'activity_dec' => 'nullable|integer|min:0|max:1000',
                'notes' => 'nullable|string',
            ]);

            $item->update($validated);
            $item->load(['category', 'budgetCodeRelation']);

            // Update parent workplan budget
            $workplan = KPIWorkPlan::find($workplanId);
            if ($workplan) {
                $workplan->updateBudgetTotal();
            }

            return response()->json([
                'success' => true,
                'message' => 'Budget item updated successfully',
                'data' => $item
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete budget item
     */
    public function destroy(Request $request, $workplanId, $itemId)
    {
        try {
            $item = WorkplanBudgetItem::where('kpi_workplan_id', $workplanId)
                ->findOrFail($itemId);

            // Check if item can be edited (deleted)
            if (!$item->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This item cannot be deleted because it has been approved'
                ], 403);
            }

            $item->delete();

            // Update parent workplan budget
            $workplan = KPIWorkPlan::find($workplanId);
            if ($workplan) {
                $workplan->updateBudgetTotal();
            }

            return response()->json([
                'success' => true,
                'message' => 'Budget item deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete item: ' . $e->getMessage()
            ], 500);
        }
    }
}
