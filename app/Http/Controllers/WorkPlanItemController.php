<?php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\BudgetCode;
use App\Models\KPIWorkPlan;
use App\Models\WorkplanBudgetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkPlanItemController extends Controller
{
    /**
     * Display work plan items page
     */
    public function index(Request $request, $id)
    {
        $workplan = KPIWorkPlan::findOrFail($id);
        
        return view('pages.work-plan.work-plan-item', compact('workplan'));
    }

    /**
     * Get budget categories (parent level 1 and children level 2)
     */
    public function getCategories(Request $request, $id)
    {
        try {
            $workplan = KPIWorkPlan::findOrFail($id);
            
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
    public function getItems(Request $request, $id)
    {
        try {
            $categoryId = $request->input('category_id');
            
            $items = WorkplanBudgetItem::with(['category', 'budgetCodeRelation', 'approver'])
                ->where('kpi_workplan_id', $id)
                ->where('budget_category_id', $categoryId)
                ->orderBy('sort_order')
                ->get();

            // Get available budget codes
            $budgetCodes = BudgetCode::active()->orderBy('code')->get();

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
    public function store(Request $request, $id)
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

            $validated['kpi_workplan_id'] = $id;
            $validated['status'] = 'draft';
            
            // Set sort order
            $maxOrder = WorkplanBudgetItem::where('kpi_workplan_id', $id)
                ->where('budget_category_id', $validated['budget_category_id'])
                ->max('sort_order');
            $validated['sort_order'] = ($maxOrder ?? 0) + 1;

            $item = WorkplanBudgetItem::create($validated);
            $item->load(['category', 'budgetCodeRelation']);

            // Update parent workplan budget
            $workplan = KPIWorkPlan::find($id);
            if ($workplan) {
                $workplan->updateBudgetFromItems();
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
    public function update(Request $request, $id, $itemId)
    {
        try {
            $item = WorkplanBudgetItem::where('kpi_workplan_id', $id)
                ->findOrFail($itemId);

            // Check if item can be edited
            if (!$item->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This item cannot be edited in its current status'
                ], 403);
            }

            $validated = $request->validate([
                'category' => 'required|in:Routine,Carry Over,Turn Around,Multi Year',
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
            $workplan = KPIWorkPlan::find($id);
            if ($workplan) {
                $workplan->updateBudgetFromItems();
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
    public function destroy(Request $request, $id, $itemId)
    {
        try {
            $item = WorkplanBudgetItem::where('kpi_workplan_id', $id)
                ->findOrFail($itemId);

            // Check if item can be edited (deleted)
            if (!$item->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This item cannot be deleted in its current status'
                ], 403);
            }

            $item->delete();

            // Update parent workplan budget
            $workplan = KPIWorkPlan::find($id);
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
}
