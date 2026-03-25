<?php

namespace App\Http\Controllers;

use App\Services\BudgetUserService\BudgetUserService;
use App\Services\LogService\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BudgetUserController extends Controller
{
    protected BudgetUserService $budgetUserService;
    protected LogService $logService;

    public function __construct(BudgetUserService $budgetUserService, LogService $logService)
    {
        $this->budgetUserService = $budgetUserService;
        $this->logService = $logService;
    }

    /**
     * Display budget user page
     */
    public function index(Request $request)
    {
        $result    = $this->budgetUserService->getDivisions();
        $divisions = $result['data'];
        $years     = range(date('Y') + 2, date('Y') - 5);

        return view('pages.budget.budget-user', compact('years', 'divisions'));
    }

    /**
     * Get divisions for dropdown (AJAX)
     */
    public function getDivisions(Request $request)
    {
        try {
            $result = $this->budgetUserService->getDivisions();

            return response()->json($result);
        } catch (\Exception $e) {
            $this->logService->create('Error loading divisions: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to load divisions: ' . $e->getMessage(),
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
            $year       = $request->input('year');

            if (! $divisionId || ! $year) {
                return response()->json([
                    'success' => false,
                    'message' => 'Division and Year are required',
                ], 400);
            }

            $result = $this->budgetUserService->getAllItems((int) $divisionId, (int) $year);

            return response()->json($result);
        } catch (\Exception $e) {
            $this->logService->create('Error loading all items: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'division_id' => $request->input('division_id'),
                'year' => $request->input('year'),
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to load items: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get budget categories (parent only)
     */
    public function getBudgetCategories(Request $request)
    {
        try {
            $result = $this->budgetUserService->getBudgetCategories();

            return response()->json($result);
        } catch (\Exception $e) {
            $this->logService->create('Error loading budget categories: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to load budget categories',
            ], 500);
        }
    }

    /**
     * Get cost centers from budget codes
     */
    public function getCostCenters(Request $request)
    {
        try {
            $result = $this->budgetUserService->getCostCenters();

            return response()->json($result);
        } catch (\Exception $e) {
            $this->logService->create('Error loading cost centers: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to load cost centers',
            ], 500);
        }
    }

    /**
     * Get suppliers
     */
    public function getSuppliers(Request $request)
    {
        try {
            $result = $this->budgetUserService->getSuppliers();

            return response()->json($result);
        } catch (\Exception $e) {
            $this->logService->create('Error loading suppliers: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to load suppliers',
            ], 500);
        }
    }

    /**
     * Get budget codes filtered by logged-in user's department(s)
     */
    public function getBudgetCodes(Request $request)
    {
        try {
            $result = $this->budgetUserService->getBudgetCodes();

            return response()->json($result);
        } catch (\Exception $e) {
            $this->logService->create('Error loading budget codes: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to load budget codes',
            ], 500);
        }
    }

    /**
     * Get stock codes
     */
    public function getStockCodes(Request $request)
    {
        try {
            $result = $this->budgetUserService->getStockCodes();

            return response()->json($result);
        } catch (\Exception $e) {
            $this->logService->create('Error loading stock codes: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to load stock codes',
            ], 500);
        }
    }

    /**
     * Get units
     */
    public function getUnits(Request $request)
    {
        try {
            $result = $this->budgetUserService->getUnits();

            return response()->json($result);
        } catch (\Exception $e) {
            $this->logService->create('Error loading units: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to load units',
            ], 500);
        }
    }

    /**
     * Search budget codes by query (server-side search, max 50 results)
     */
    public function searchBudgetCodes(Request $request)
    {
        try {
            $query  = $request->input('q', '');
            $limit  = min((int) $request->input('limit', 50), 100);
            $result = $this->budgetUserService->searchBudgetCodes($query, $limit);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search budget codes',
                'data'    => [],
            ], 500);
        }
    }

    /**
     * Search stock codes by query (server-side search, max 50 results)
     */
    public function searchStockCodes(Request $request)
    {
        try {
            $query  = $request->input('q', '');
            $limit  = min((int) $request->input('limit', 50), 100);
            $result = $this->budgetUserService->searchStockCodes($query, $limit);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search stock codes',
                'data'    => [],
            ], 500);
        }
    }

    /**
     * Get a single budget code by exact code value (for edit form pre-population)
     */
    public function getBudgetCodeByCode(Request $request)
    {
        try {
            $code   = $request->input('code', '');
            $result = $this->budgetUserService->getBudgetCodeByCode($code);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data'    => null,
            ], 500);
        }
    }

    /**
     * Get a single stock code by exact code value (for edit form pre-population)
     */
    public function getStockCodeByCode(Request $request)
    {
        try {
            $code   = $request->input('code', '');
            $result = $this->budgetUserService->getStockCodeByCode($code);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data'    => null,
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
                'kpi_workplan_id'              => 'required|exists:kpi_workplans,id',
                'budget_category_id'           => 'required|exists:budget_categories,id',
                'category_type'                => 'required|in:Routine,Carry Over,Turn Around,Multi Year',
                'description'                  => 'required|string',
                'stock_code'                   => 'nullable|string|max:50',
                'budget_code'                  => 'nullable|string|max:50',
                'product_line'                 => 'nullable|string|max:100',
                'cost_center'                  => 'nullable|string|max:50',
                'beg_balance'                  => 'nullable|string|max:100',
                'supplier_id'                  => 'nullable|integer',
                'supplier_name'                => 'nullable|string|max:255',
                'cons_rate'                    => 'nullable|string|max:100',
                'unit_id'                      => 'nullable|integer',
                'unit_name'                    => 'nullable|string|max:50',
                'total'                        => 'nullable|numeric',
                'price_estimation'             => 'nullable|numeric',
                'price_estimation_description' => 'nullable|string|max:255',
                'activity_jan'                 => 'nullable|integer|min:0',
                'activity_feb'                 => 'nullable|integer|min:0',
                'activity_mar'                 => 'nullable|integer|min:0',
                'activity_apr'                 => 'nullable|integer|min:0',
                'activity_may'                 => 'nullable|integer|min:0',
                'activity_jun'                 => 'nullable|integer|min:0',
                'activity_jul'                 => 'nullable|integer|min:0',
                'activity_aug'                 => 'nullable|integer|min:0',
                'activity_sep'                 => 'nullable|integer|min:0',
                'activity_oct'                 => 'nullable|integer|min:0',
                'activity_nov'                 => 'nullable|integer|min:0',
                'activity_dec'                 => 'nullable|integer|min:0',
            ]);

            $result = $this->budgetUserService->createItem($validated);

            return response()->json($result, $result['success'] ? 200 : 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', array_flatten($e->errors())),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            $this->logService->create('Error creating item: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'input' => $request->all(),
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to create item: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update budget item
     */
    public function updateItem(Request $request, $itemId)
    {
        try {
            $validated = $request->validate([
                'kpi_workplan_id'              => 'nullable|exists:kpi_workplans,id',
                'budget_category_id'           => 'nullable|exists:budget_categories,id',
                'category_type'                => 'required|in:Routine,Carry Over,Turn Around,Multi Year',
                'description'                  => 'required|string',
                'stock_code'                   => 'nullable|string|max:50',
                'budget_code'                  => 'nullable|string|max:50',
                'product_line'                 => 'nullable|string|max:100',
                'cost_center'                  => 'nullable|string|max:50',
                'beg_balance'                  => 'nullable|string|max:100',
                'supplier_id'                  => 'nullable|integer',
                'supplier_name'                => 'nullable|string|max:255',
                'cons_rate'                    => 'nullable|string|max:100',
                'unit_id'                      => 'nullable|integer',
                'unit_name'                    => 'nullable|string|max:50',
                'total'                        => 'nullable|numeric',
                'price_estimation'             => 'nullable|numeric',
                'price_estimation_description' => 'nullable|string|max:255',
                'activity_jan'                 => 'nullable|integer|min:0',
                'activity_feb'                 => 'nullable|integer|min:0',
                'activity_mar'                 => 'nullable|integer|min:0',
                'activity_apr'                 => 'nullable|integer|min:0',
                'activity_may'                 => 'nullable|integer|min:0',
                'activity_jun'                 => 'nullable|integer|min:0',
                'activity_jul'                 => 'nullable|integer|min:0',
                'activity_aug'                 => 'nullable|integer|min:0',
                'activity_sep'                 => 'nullable|integer|min:0',
                'activity_oct'                 => 'nullable|integer|min:0',
                'activity_nov'                 => 'nullable|integer|min:0',
                'activity_dec'                 => 'nullable|integer|min:0',
            ]);

            $result = $this->budgetUserService->updateItem((int) $itemId, $validated);

            return response()->json($result, $result['success'] ? 200 : 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', array_flatten($e->errors())),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            $this->logService->create('Error updating item: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'item_id' => $itemId,
                'input' => $request->all(),
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to update item: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete budget item
     */
    public function destroyItem(Request $request, $itemId)
    {
        try {
            $result = $this->budgetUserService->deleteItem((int) $itemId);

            return response()->json($result, $result['success'] ? 200 : 403);
        } catch (\Exception $e) {
            $this->logService->create('Error deleting item: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'item_id' => $itemId,
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete item: ' . $e->getMessage(),
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
            $year       = $request->input('year');

            if (! $divisionId || ! $year) {
                return response()->json([
                    'success' => false,
                    'message' => 'Division and year are required',
                ], 400);
            }

            $result = $this->budgetUserService->getWorkplansDropdown((int) $divisionId, (int) $year);

            return response()->json($result);
        } catch (\Exception $e) {
            $this->logService->create('Error loading workplans dropdown: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'division_id' => $request->input('division_id'),
                'year' => $request->input('year'),
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to load workplans: ' . $e->getMessage(),
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
            $year       = $request->input('year');

            if (! $divisionId || ! $year) {
                return response()->json([
                    'success' => false,
                    'message' => 'Division and Year are required',
                ], 400);
            }

            $result = $this->budgetUserService->getWorkplans((int) $divisionId, (int) $year);

            return response()->json($result);
        } catch (\Exception $e) {
            $this->logService->create('Error loading workplans: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'division_id' => $request->input('division_id'),
                'year' => $request->input('year'),
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to load workplans: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get budget categories (parent level 1 and children level 2)
     */
    public function getCategories(Request $request, $workplanId)
    {
        try {
            $result = $this->budgetUserService->getCategoriesByWorkplan((int) $workplanId);

            return response()->json($result);
        } catch (\Exception $e) {
            $this->logService->create('Error loading categories: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'workplan_id' => $workplanId,
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to load categories: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get budget items by category
     */
    public function getItems(Request $request, $workplanId)
    {
        try {
            $categoryId = $request->input('category_id') ? (int) $request->input('category_id') : null;
            $result     = $this->budgetUserService->getItemsByWorkplan((int) $workplanId, $categoryId);

            return response()->json($result);
        } catch (\Exception $e) {
            $this->logService->create('Error loading items: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'workplan_id' => $workplanId,
                'category_id' => $request->input('category_id'),
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to load items: ' . $e->getMessage(),
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
                'budget_category_id'           => 'required|exists:budget_categories,id',
                'category_type'                => 'required|in:Routine,Carry Over,Turn Around,Multi Year',
                'description'                  => 'required|string',
                'stock_code'                   => 'nullable|string|max:50',
                'budget_code'                  => 'nullable|string|max:50',
                'product_line'                 => 'nullable|string|max:100',
                'cost_center'                  => 'nullable|string|max:50',
                'beg_balance'                  => 'nullable|string|max:100',
                'cons_rate'                    => 'nullable|string|max:100',
                'unit'                         => 'nullable|string|max:50',
                'total'                        => 'nullable|numeric',
                'price_estimation'             => 'nullable|numeric',
                'price_estimation_description' => 'nullable|string|max:255',
                'activity_jan'                 => 'nullable|integer|min:0|max:1000',
                'activity_feb'                 => 'nullable|integer|min:0|max:1000',
                'activity_mar'                 => 'nullable|integer|min:0|max:1000',
                'activity_apr'                 => 'nullable|integer|min:0|max:1000',
                'activity_may'                 => 'nullable|integer|min:0|max:1000',
                'activity_jun'                 => 'nullable|integer|min:0|max:1000',
                'activity_jul'                 => 'nullable|integer|min:0|max:1000',
                'activity_aug'                 => 'nullable|integer|min:0|max:1000',
                'activity_sep'                 => 'nullable|integer|min:0|max:1000',
                'activity_oct'                 => 'nullable|integer|min:0|max:1000',
                'activity_nov'                 => 'nullable|integer|min:0|max:1000',
                'activity_dec'                 => 'nullable|integer|min:0|max:1000',
                'notes'                        => 'nullable|string',
            ]);

            $result = $this->budgetUserService->createItemForWorkplan((int) $workplanId, $validated);

            return response()->json($result, $result['success'] ? 200 : 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', array_flatten($e->errors())),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            $this->logService->create('Error creating item: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'workplan_id' => $workplanId,
                'input' => $request->all(),
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to create item: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update budget item
     */
    public function update(Request $request, $workplanId, $itemId)
    {
        try {
            $validated = $request->validate([
                'budget_category_id'           => 'required|exists:budget_categories,id',
                'category_type'                => 'required|in:Routine,Carry Over,Turn Around,Multi Year',
                'description'                  => 'required|string',
                'stock_code'                   => 'nullable|string|max:50',
                'budget_code'                  => 'nullable|string|max:50',
                'product_line'                 => 'nullable|string|max:100',
                'cost_center'                  => 'nullable|string|max:50',
                'beg_balance'                  => 'nullable|string|max:100',
                'cons_rate'                    => 'nullable|string|max:100',
                'unit'                         => 'nullable|string|max:50',
                'total'                        => 'nullable|numeric',
                'price_estimation'             => 'nullable|numeric',
                'price_estimation_description' => 'nullable|string|max:255',
                'activity_jan'                 => 'nullable|integer|min:0|max:1000',
                'activity_feb'                 => 'nullable|integer|min:0|max:1000',
                'activity_mar'                 => 'nullable|integer|min:0|max:1000',
                'activity_apr'                 => 'nullable|integer|min:0|max:1000',
                'activity_may'                 => 'nullable|integer|min:0|max:1000',
                'activity_jun'                 => 'nullable|integer|min:0|max:1000',
                'activity_jul'                 => 'nullable|integer|min:0|max:1000',
                'activity_aug'                 => 'nullable|integer|min:0|max:1000',
                'activity_sep'                 => 'nullable|integer|min:0|max:1000',
                'activity_oct'                 => 'nullable|integer|min:0|max:1000',
                'activity_nov'                 => 'nullable|integer|min:0|max:1000',
                'activity_dec'                 => 'nullable|integer|min:0|max:1000',
                'notes'                        => 'nullable|string',
            ]);

            $result = $this->budgetUserService->updateItemForWorkplan((int) $workplanId, (int) $itemId, $validated);

            return response()->json($result, $result['success'] ? 200 : 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', array_flatten($e->errors())),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            $this->logService->create('Error updating item: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'workplan_id' => $workplanId,
                'item_id' => $itemId,
                'input' => $request->all(),
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to update item: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete budget item
     */
    public function destroy(Request $request, $workplanId, $itemId)
    {
        try {
            $result = $this->budgetUserService->deleteItemForWorkplan((int) $workplanId, (int) $itemId);

            return response()->json($result, $result['success'] ? 200 : 403);
        } catch (\Exception $e) {
            $this->logService->create('Error deleting item: ' . $e->getMessage(), [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'workplan_id' => $workplanId,
                'item_id' => $itemId,
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete item: ' . $e->getMessage(),
            ], 500);
        }
    }
}
