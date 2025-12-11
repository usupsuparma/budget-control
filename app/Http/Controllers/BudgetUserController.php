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
     * Get all budget items by division and year
     */
    public function getAllItems(Request $request)
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

            // Get all workplans for this division and year
            $workplans = KPIWorkPlan::with([
                    'kpiDepartment' => function($query) {
                        $query->with(['department', 'kpiDivision']);
                    },
                    'kpiSection' => function($query) {
                        $query->with('section');
                    }
                ])
                ->where('year', $year)
                ->whereHas('kpiDepartment.kpiDivision', function($query) use ($divisionId) {
                    $query->where('division_id', $divisionId);
                })
                ->get();

            // Get all budget items from these workplans
            $workplanIds = $workplans->pluck('id')->toArray();
            
            $items = WorkplanBudgetItem::with(['category', 'budgetCodeRelation', 'approver', 'workplan'])
                ->whereIn('kpi_workplan_id', $workplanIds)
                ->orderBy('kpi_workplan_id')
                ->orderBy('sort_order')
                ->get();

            // Get available budget codes
            $budgetCodes = BudgetCode::active()
                ->select('id', 'stock_code', 'name', 'inchargeCode')
                ->orderBy('stock_code')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $items,
                'workplans' => $workplans,
                'totalWorkplans' => $workplans->count(),
                'budgetCodes' => $budgetCodes
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading all items: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get budget categories (parent only)
     */
    public function getBudgetCategories(Request $request)
    {
        try {
            $categories = BudgetCategory::whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'code']);

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading budget categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load budget categories'
            ], 500);
        }
    }

    /**
     * Get cost centers from budget codes
     */
    public function getCostCenters(Request $request)
    {
        try {
            $costCenters = BudgetCode::whereNotNull('inchargeCode')
                ->where('inchargeCode', '!=', '')
                ->distinct()
                ->pluck('inchargeCode')
                ->sort()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $costCenters
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading cost centers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load cost centers'
            ], 500);
        }
    }

    /**
     * Get suppliers
     */
    public function getSuppliers(Request $request)
    {
        try {
            $suppliers = \App\Models\Supplier::whereNotNull('supplier')
                ->where('supplier', '!=', '')
                ->select('id', 'supplier')
                ->orderBy('supplier')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $suppliers
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading suppliers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load suppliers'
            ], 500);
        }
    }

    /**
     * Get units
     */
    public function getUnits(Request $request)
    {
        try {
            $units = \App\Models\Unit::whereNotNull('unit')
                ->where('unit', '!=', '')
                ->select('id', 'unit')
                ->orderBy('unit')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $units
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading units: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load units'
            ], 500);
        }
    }

    /**
     * Store new budget item (without workplan ID initially)
     */
    public function storeItem(Request $request)
    {
        try {
            $validated = $request->validate([
                'kpi_workplan_id' => 'required|exists:kpi_workplans,id',
                'budget_category_id' => 'required|exists:budget_categories,id',
                'description' => 'required|string',
                'stock_code' => 'nullable|string|max:50',
                'budget_code' => 'nullable|string|max:50',
                'product_line' => 'nullable|string|max:100',
                'cost_center' => 'nullable|string|max:50',
                'beg_balance' => 'nullable|string|max:100',
                'supplier_id' => 'nullable|integer',
                'supplier_name' => 'nullable|string|max:255',
                'cons_rate' => 'nullable|string|max:100',
                'unit_id' => 'nullable|integer',
                'unit_name' => 'nullable|string|max:50',
                'total' => 'nullable|numeric',
                'price_estimation' => 'nullable|numeric',
                'price_estimation_description' => 'nullable|string|max:255',
                'activity_jan' => 'nullable|integer|min:0',
                'activity_feb' => 'nullable|integer|min:0',
                'activity_mar' => 'nullable|integer|min:0',
                'activity_apr' => 'nullable|integer|min:0',
                'activity_may' => 'nullable|integer|min:0',
                'activity_jun' => 'nullable|integer|min:0',
                'activity_jul' => 'nullable|integer|min:0',
                'activity_aug' => 'nullable|integer|min:0',
                'activity_sep' => 'nullable|integer|min:0',
                'activity_oct' => 'nullable|integer|min:0',
                'activity_nov' => 'nullable|integer|min:0',
                'activity_dec' => 'nullable|integer|min:0',
            ]);

            $validated['status'] = 'draft';
            
            // Set sort order
            $maxOrder = WorkplanBudgetItem::where('kpi_workplan_id', $validated['kpi_workplan_id'])
                ->max('sort_order');
            $validated['sort_order'] = ($maxOrder ?? 0) + 1;

            $item = WorkplanBudgetItem::create($validated);
            $item->load(['category', 'budgetCodeRelation', 'workplan']);

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
    public function updateItem(Request $request, $itemId)
    {
        try {
            $item = WorkplanBudgetItem::findOrFail($itemId);

            // Check if item can be edited
            if (!$item->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This item cannot be edited in its current status'
                ], 403);
            }

            $validated = $request->validate([
                'kpi_workplan_id' => 'nullable|exists:kpi_workplans,id',
                'budget_category_id' => 'nullable|exists:budget_categories,id',
                'description' => 'required|string',
                'stock_code' => 'nullable|string|max:50',
                'budget_code' => 'nullable|string|max:50',
                'product_line' => 'nullable|string|max:100',
                'cost_center' => 'nullable|string|max:50',
                'beg_balance' => 'nullable|string|max:100',
                'supplier_id' => 'nullable|integer',
                'supplier_name' => 'nullable|string|max:255',
                'cons_rate' => 'nullable|string|max:100',
                'unit_id' => 'nullable|integer',
                'unit_name' => 'nullable|string|max:50',
                'total' => 'nullable|numeric',
                'price_estimation' => 'nullable|numeric',
                'price_estimation_description' => 'nullable|string|max:255',
                'activity_jan' => 'nullable|integer|min:0',
                'activity_feb' => 'nullable|integer|min:0',
                'activity_mar' => 'nullable|integer|min:0',
                'activity_apr' => 'nullable|integer|min:0',
                'activity_may' => 'nullable|integer|min:0',
                'activity_jun' => 'nullable|integer|min:0',
                'activity_jul' => 'nullable|integer|min:0',
                'activity_aug' => 'nullable|integer|min:0',
                'activity_sep' => 'nullable|integer|min:0',
                'activity_oct' => 'nullable|integer|min:0',
                'activity_nov' => 'nullable|integer|min:0',
                'activity_dec' => 'nullable|integer|min:0',
            ]);

            $item->update($validated);
            $item->load(['category', 'budgetCodeRelation', 'workplan']);

            // Update parent workplan budget
            $workplan = $item->workplan;
            if ($workplan) {
                $workplan->updateBudgetFromItems();
            }

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
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
    public function destroyItem(Request $request, $itemId)
    {
        try {
            $item = WorkplanBudgetItem::findOrFail($itemId);

            // Check if item can be edited (deleted)
            if (!$item->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This item cannot be deleted in its current status'
                ], 403);
            }

            $workplan = $item->workplan;
            $item->delete();

            // Update parent workplan budget
            if ($workplan) {
                $workplan->updateBudgetFromItems();
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

    /**
     * Get workplans for dropdown (AJAX) - Department and Section only
     */
    public function getWorkplansDropdown(Request $request)
    {
        try {
            $divisionId = $request->input('division_id');
            $year = $request->input('year');

            if (!$divisionId || !$year) {
                return response()->json([
                    'success' => false,
                    'message' => 'Division and year are required'
                ], 400);
            }

            // Get workplans for department and section only
            $workplans = KPIWorkPlan::with(['kpiDepartment.department', 'kpiSection.section'])
                ->where('year', $year)
                ->whereIn('kpi_type', ['department', 'section'])
                ->get()
                ->filter(function($workplan) use ($divisionId) {
                    if ($workplan->kpi_type === 'department') {
                        return $workplan->kpiDepartment && 
                               $workplan->kpiDepartment->department && 
                               $workplan->kpiDepartment->department->division_id == $divisionId;
                    } else if ($workplan->kpi_type === 'section') {
                        return $workplan->kpiSection && 
                               $workplan->kpiSection->section && 
                               $workplan->kpiSection->section->department &&
                               $workplan->kpiSection->section->department->division_id == $divisionId;
                    }
                    return false;
                })
                ->values()
                ->map(function($workplan) {
                    return [
                        'id' => $workplan->id,
                        'activity' => $workplan->activity,
                        'kpi_type' => $workplan->kpi_type,
                        'kpi_name' => $workplan->kpi_type === 'department' 
                            ? ($workplan->kpiDepartment->department->name ?? '-')
                            : ($workplan->kpiSection->section->name ?? '-'),
                        'year' => $workplan->year
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $workplans
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading workplans dropdown: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load workplans: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get workplans for dropdown (AJAX)
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
