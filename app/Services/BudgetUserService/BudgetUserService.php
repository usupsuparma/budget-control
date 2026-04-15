<?php

namespace App\Services\BudgetUserService;

/**
 * Service interface for Budget User module operations.
 *
 * Handles all business logic for budget user page:
 * data loading, dropdown data, and CRUD operations on budget items.
 */
interface BudgetUserService
{
    /**
     * Get unique divisions from KPI Division data.
     *
     * @return array ['success' => bool, 'data' => mixed]
     */
    public function getDivisions(): array;

    /**
     * Get all budget items for a division and year with related data.
     *
     * @param  int  $divisionId
     * @param  int  $year
     * @return array ['success' => bool, 'data' => mixed, 'workplans' => mixed, 'totalWorkplans' => int, 'budgetCodes' => mixed, 'stockCodes' => mixed, 'currentEmploymentId' => int|null]
     */
    public function getAllItems(int $divisionId, int $year): array;

    /**
     * Get budget categories (parent level only).
     *
     * @return array ['success' => bool, 'data' => mixed]
     */
    public function getBudgetCategories(): array;

    /**
     * Get unique cost centers from budget codes.
     *
     * @return array ['success' => bool, 'data' => mixed]
     */
    public function getCostCenters(): array;

    /**
     * Get active suppliers.
     *
     * @return array ['success' => bool, 'data' => mixed]
     */
    public function getSuppliers(): array;

    /**
     * Get active budget codes filtered by the logged-in user's department(s).
     *
     * @return array ['success' => bool, 'data' => mixed]
     */
    public function getBudgetCodes(): array;

    /**
     * Get active stock codes filtered by the logged-in user's department(s).
     *
     * @return array ['success' => bool, 'data' => mixed]
     */
    public function getStockCodes(): array;

    /**
     * Get active units.
     *
     * @return array ['success' => bool, 'data' => mixed]
     */
    public function getUnits(): array;

    /**
     * Search budget codes by query string (server-side search, max 50 results).
     *
     * @param  string  $query
     * @param  int     $limit
     * @return array ['success' => bool, 'data' => mixed]
     */
    public function searchBudgetCodes(string $query, int $limit = 50): array;

    /**
     * Search stock codes by query string (server-side search, paginated).
     *
     * @param  string  $query
     * @param  int     $limit
     * @param  int     $page
     * @return array ['success' => bool, 'data' => mixed, 'has_more' => bool, 'page' => int, 'total' => int]
     */
    public function searchStockCodes(string $query, int $limit = 10, int $page = 1): array;

    /**
     * Get a single budget code by its exact code value (for pre-population in edit forms).
     *
     * @param  string  $code
     * @return array ['success' => bool, 'data' => mixed]
     */
    public function getBudgetCodeByCode(string $code): array;

    /**
     * Get a single stock code by its exact code value (for pre-population in edit forms).
     *
     * @param  string  $code
     * @return array ['success' => bool, 'data' => mixed]
     */
    public function getStockCodeByCode(string $code): array;

    /**
     * Get workplans for dropdown (department and section only).
     *
     * @param  int  $divisionId
     * @param  int  $year
     * @return array ['success' => bool, 'data' => mixed]
     */
    public function getWorkplansDropdown(int $divisionId, int $year): array;

    /**
     * Get all workplans for a division and year.
     *
     * @param  int  $divisionId
     * @param  int  $year
     * @return array ['success' => bool, 'data' => mixed]
     */
    public function getWorkplans(int $divisionId, int $year): array;

    /**
     * Get budget categories with children for a specific workplan.
     *
     * @param  int  $workplanId
     * @return array ['success' => bool, 'data' => mixed, 'workplan' => mixed]
     */
    public function getCategoriesByWorkplan(int $workplanId): array;

    /**
     * Get budget items for a workplan, optionally filtered by category.
     *
     * @param  int       $workplanId
     * @param  int|null  $categoryId
     * @return array ['success' => bool, 'data' => mixed, 'budgetCodes' => mixed]
     */
    public function getItemsByWorkplan(int $workplanId, ?int $categoryId): array;

    /**
     * Create a new budget item (kpi_workplan_id included in data).
     *
     * @param  array  $data  Validated data including kpi_workplan_id
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function createItem(array $data): array;

    /**
     * Update a budget item by ID.
     *
     * @param  int    $itemId
     * @param  array  $data  Validated data
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function updateItem(int $itemId, array $data): array;

    /**
     * Delete a budget item by ID.
     *
     * @param  int  $itemId
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteItem(int $itemId): array;

    /**
     * Create a budget item scoped to a specific workplan.
     *
     * @param  int    $workplanId
     * @param  array  $data  Validated data
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function createItemForWorkplan(int $workplanId, array $data): array;

    /**
     * Update a budget item scoped to a specific workplan.
     *
     * @param  int    $workplanId
     * @param  int    $itemId
     * @param  array  $data  Validated data
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function updateItemForWorkplan(int $workplanId, int $itemId, array $data): array;

    /**
     * Delete a budget item scoped to a specific workplan.
     *
     * @param  int  $workplanId
     * @param  int  $itemId
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteItemForWorkplan(int $workplanId, int $itemId): array;
}
