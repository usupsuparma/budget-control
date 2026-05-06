<?php

namespace App\Services\BudgetUserService;

use App\Models\BudgetCategory;
use App\Models\BudgetCode;
use App\Models\KPIDivision;
use App\Models\KPIWorkPlan;
use App\Models\StockCode;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\WorkplanBudgetItem;
use App\Services\LogService\LogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BudgetUserServiceImpl implements BudgetUserService
{
    protected LogService $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    public function getDivisions(): array
    {
        $kpiDivisions = KPIDivision::with('division')
            ->select('division_id')
            ->distinct()
            ->get();

        $divisions = $kpiDivisions->map(fn($kpi) => $kpi->division)
            ->filter()
            ->unique('id')
            ->values();

        return ['success' => true, 'data' => $divisions];
    }

    public function getAllItems(int $divisionId, int $year): array
    {
        $workplans = KPIWorkPlan::with([
            'KPIDepartment' => fn($q) => $q->with(['department', 'kpiDivision']),
            'kpiSection'    => fn($q) => $q->with(['section.department']),
        ])
            ->where('year', $year)
            ->where(function ($query) use ($divisionId) {
            $query->whereHas('KPIDepartment.kpiDivision', fn($q) => $q->where('division_id', $divisionId))
                    ->orWhere(fn($q) => $q->whereHas(
                        'kpiSection.section.department',
                        fn($dq) => $dq->where('division_id', $divisionId)
                    ));
            })
            ->get();

        $workplanIds = $workplans->pluck('id')->toArray();

        $items = WorkplanBudgetItem::with([
            'category',
            'budgetCodeRelation',
            'stockCodeRelation',
            'approver',
            'workplan',
            'approvalRequest.details.employment.employee',
            'verificationCandidates.verifier',
            'verifications',
            'executor.verifier',
        ])
            ->whereIn('kpi_workplan_id', $workplanIds)
            ->orderBy('kpi_workplan_id')
            ->orderBy('sort_order')
            ->get();

        $currentEmploymentId = null;
        $employee = Auth::user();
        if ($employee && $employee->employment) {
            $currentEmploymentId = $employee->employment->id;
        }

        $this->logService->create(
            'Loaded all budget items for division and year.',
            ['class' => __CLASS__, 'function' => __FUNCTION__, 'division_id' => $divisionId, 'year' => $year, 'user_id' => Auth::id()],
            'info'
        );

        return [
            'success'             => true,
            'data'                => $items,
            'workplans'           => $workplans,
            'totalWorkplans'      => $workplans->count(),
            'currentEmploymentId' => $currentEmploymentId,
        ];
    }

    public function getBudgetCategories(): array
    {
        $categories = Cache::remember('budget_categories_dropdown', 3600, function () {
            return BudgetCategory::whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'code']);
        });

        return ['success' => true, 'data' => $categories];
    }

    public function getCostCenters(): array
    {
        $deptCodes = session('department_codes', []);
        $cacheKey  = 'cost_centers_' . md5(implode(',', $deptCodes));

        $costCenters = Cache::remember($cacheKey, 3600, function () use ($deptCodes) {
            $query = BudgetCode::whereNotNull('inchargeCode')
                ->where('inchargeCode', '!=', '');

            if (! empty($deptCodes)) {
                $query->whereIn('inchargeCode', $deptCodes);
            }

            return $query->distinct()
                ->pluck('inchargeCode')
                ->sort()
                ->values();
        });

        return ['success' => true, 'data' => $costCenters];
    }

    public function getSuppliers(): array
    {
        $suppliers = Cache::remember('suppliers_dropdown', 3600, function () {
            return Supplier::whereNotNull('supplier')
                ->where('supplier', '!=', '')
                ->select('id', 'supplier')
                ->orderBy('supplier')
                ->get();
        });

        return ['success' => true, 'data' => $suppliers];
    }

    public function getBudgetCodes(): array
    {
        $deptCodes = session('department_codes', []);
        $cacheKey  = 'budget_codes_depts_' . md5(implode(',', $deptCodes));

        $budgetCodes = Cache::remember($cacheKey, 3600, function () use ($deptCodes) {
            $query = BudgetCode::active()
                ->select('id', 'budget_code', 'name', 'inchargeCode')
                ->orderBy('budget_code');

            if (! empty($deptCodes)) {
                $query->whereIn('inchargeCode', $deptCodes);
            }

            return $query->get();
        });

        return ['success' => true, 'data' => $budgetCodes];
    }

    public function getStockCodes(): array
    {
        $deptCodes = session('department_codes', []);

        if (! empty($deptCodes)) {
            $cacheKey = 'stock_codes_depts_' . md5(implode(',', $deptCodes));
            $filtered = Cache::remember($cacheKey, 3600, function () use ($deptCodes) {
                $allowedBudgetCodes = BudgetCode::active()
                    ->whereIn('inchargeCode', $deptCodes)
                    ->pluck('budget_code');

                return StockCode::where('active', 1)
                    ->whereIn('budget_code', $allowedBudgetCodes)
                    ->select('id', 'stock_code', 'name', 'unit', 'budget_code')
                    ->orderBy('stock_code')
                    ->get();
            });

            if ($filtered->isNotEmpty()) {
                return ['success' => true, 'data' => $filtered];
            }
        }

        $stockCodes = Cache::remember('stock_codes_all', 3600, function () {
            return StockCode::where('active', 1)
                ->select('id', 'stock_code', 'name', 'unit', 'budget_code')
                ->orderBy('stock_code')
                ->get();
        });

        return ['success' => true, 'data' => $stockCodes];
    }

    public function getUnits(): array
    {
        $units = Cache::remember('units_dropdown', 3600, function () {
            return Unit::whereNotNull('unit')
                ->where('unit', '!=', '')
                ->select('id', 'unit')
                ->orderBy('unit')
                ->get();
        });

        return ['success' => true, 'data' => $units];
    }

    public function searchBudgetCodes(string $query, int $limit = 10, int $page = 1): array
    {
        try {
            $deptCodes = session('department_codes', []);
            $query     = trim($query);

            $dbQuery = BudgetCode::active()
                ->select('id', 'budget_code', 'name', 'inchargeCode')
                ->where(function ($q) use ($query) {
                    if ($query !== '') {
                        $q->where('budget_code', 'LIKE', "%{$query}%")
                            ->orWhere('name', 'LIKE', "%{$query}%");
                    }
                })
                ->orderBy('budget_code');

            if (! empty($deptCodes)) {
                $dbQuery->whereIn('inchargeCode', $deptCodes);
            }

            $total  = $dbQuery->count();
            $offset = ($page - 1) * $limit;
            $data   = $dbQuery->offset($offset)->limit($limit)->get();

            return [
                'success'  => true,
                'data'     => $data,
                'has_more' => ($offset + $limit) < $total,
                'page'     => $page,
                'total'    => $total,
            ];
        } catch (\Throwable $th) {
            return ['success' => false, 'message' => 'An error occurred while searching budget codes: ' . $th->getMessage()];
        }
    }

    public function searchStockCodes(string $query, int $limit = 10, int $page = 1): array
    {
        try {
            $deptCodes = session('department_codes', []);
            $query     = trim($query);

            $dbQuery = StockCode::where('active', 1)
                ->select('id', 'stock_code', 'name', 'unit', 'budget_code', 'product_line')
                ->where(function ($q) use ($query) {
                    if ($query !== '') {
                        $q->where('stock_code', 'LIKE', "%{$query}%")
                            ->orWhere('name', 'LIKE', "%{$query}%");
                    }
                })
                ->orderBy('stock_code');

            if (! empty($deptCodes)) {
                $allowedBudgetCodes = Cache::remember(
                    'allowed_budget_codes_' . md5(implode(',', $deptCodes)),
                    3600,
                    fn() => BudgetCode::active()->whereIn('inchargeCode', $deptCodes)->pluck('budget_code')
                );
                if ($allowedBudgetCodes->isNotEmpty()) {
                    $dbQuery->whereIn('budget_code', $allowedBudgetCodes);
                }
            }

            $total  = $dbQuery->count();
            $offset = ($page - 1) * $limit;
            $data   = $dbQuery->offset($offset)->limit($limit)->get();

            return [
                'success'  => true,
                'data'     => $data,
                'has_more' => ($offset + $limit) < $total,
                'page'     => $page,
                'total'    => $total,
            ];
        } catch (\Throwable $th) {
            return ['success' => false, 'message' => 'An error occurred while searching stock codes: ' . $th->getMessage()];
        }
    }

    public function getBudgetCodeByCode(string $code): array
    {
        $item = BudgetCode::active()
            ->select('id', 'budget_code', 'name', 'inchargeCode')
            ->where('budget_code', $code)
            ->first();

        return ['success' => true, 'data' => $item];
    }

    public function getStockCodeByCode(string $code): array
    {
        $item = StockCode::where('active', 1)
            ->select('id', 'stock_code', 'name', 'unit', 'budget_code', 'product_line')
            ->where('stock_code', $code)
            ->first();

        return ['success' => true, 'data' => $item];
    }

    public function getWorkplansDropdown(int $divisionId, int $year): array
    {
        $workplans = KPIWorkPlan::with(['KPIDepartment.department', 'kpiSection.section'])
            ->where('year', $year)
            ->whereIn('kpi_type', ['department', 'section'])
            ->get()
            ->filter(function ($workplan) use ($divisionId) {
                if ($workplan->kpi_type === 'department') {
                return $workplan->KPIDepartment
                    && $workplan->KPIDepartment->department
                    && $workplan->KPIDepartment->department->division_id == $divisionId;
                }
                if ($workplan->kpi_type === 'section') {
                    return $workplan->kpiSection
                        && $workplan->kpiSection->section
                        && $workplan->kpiSection->section->department
                        && $workplan->kpiSection->section->department->division_id == $divisionId;
                }
                return false;
            })
            ->values()
            ->map(fn($workplan) => [
                'id'       => $workplan->id,
                'activity' => $workplan->activity,
                'kpi_type' => $workplan->kpi_type,
                'kpi_name' => $workplan->kpi_type === 'department'
                ? ($workplan->KPIDepartment->department->name ?? '-')
                    : ($workplan->kpiSection->section->name ?? '-'),
                'year'     => $workplan->year,
            ]);

        return ['success' => true, 'data' => $workplans];
    }

    public function getWorkplans(int $divisionId, int $year): array
    {
        $workplans = KPIWorkPlan::with([
            'KPIDepartment' => fn($q) => $q->with(['department', 'kpiDivision']),
            'kpiSection'    => fn($q) => $q->with(['section', 'KPIDepartment.kpiDivision']),
        ])
            ->where('year', $year)
            ->where(function ($query) use ($divisionId) {
                $query->where(function ($q) use ($divisionId) {
                    $q->where('kpi_type', 'department')
                    ->whereHas('KPIDepartment', fn($dept) => $dept->whereHas(
                            'kpiDivision',
                            fn($div) => $div->where('division_id', $divisionId)
                        ));
                })->orWhere(function ($q) use ($divisionId) {
                    $q->where('kpi_type', 'section')
                        ->whereHas('kpiSection', fn($sect) => $sect->whereHas(
                    'KPIDepartment',
                            fn($dept) => $dept->whereHas(
                                'kpiDivision',
                                fn($div) => $div->where('division_id', $divisionId)
                            )
                        ));
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return ['success' => true, 'data' => $workplans];
    }

    public function getCategoriesByWorkplan(int $workplanId): array
    {
        $workplan = KPIWorkPlan::findOrFail($workplanId);

        $categories = BudgetCategory::with([
            'children' => fn($q) => $q->where('level', 2)->where('is_active', true)->orderBy('sort_order'),
        ])
            ->where('level', 1)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return ['success' => true, 'data' => $categories, 'workplan' => $workplan];
    }

    public function getItemsByWorkplan(int $workplanId, ?int $categoryId): array
    {
        $items = WorkplanBudgetItem::with(['category', 'budgetCodeRelation', 'approver'])
            ->where('kpi_workplan_id', $workplanId)
            ->where('budget_category_id', $categoryId)
            ->orderBy('sort_order')
            ->get();

        $budgetCodes = BudgetCode::active()
            ->select('id', 'budget_code', 'name', 'inchargeCode')
            ->orderBy('budget_code')
            ->get();

        return ['success' => true, 'data' => $items, 'budgetCodes' => $budgetCodes];
    }

    public function createItem(array $data): array
    {
        $data['status']     = 'draft';
        $data['sort_order'] = (WorkplanBudgetItem::where('kpi_workplan_id', $data['kpi_workplan_id'])->max('sort_order') ?? 0) + 1;

        $item = WorkplanBudgetItem::create($data);
        $item->load(['category', 'budgetCodeRelation', 'stockCodeRelation', 'workplan']);

        if ($item->workplan) {
            $item->workplan->updateBudgetFromItems();
        }

        $this->logService->create(
            'Budget item created.',
            ['class' => __CLASS__, 'function' => __FUNCTION__, 'item_id' => $item->id, 'user_id' => Auth::id()],
            'info'
        );

        return ['success' => true, 'message' => 'Budget item created successfully', 'data' => $item];
    }

    public function updateItem(int $itemId, array $data): array
    {
        $item = WorkplanBudgetItem::findOrFail($itemId);

        if (! $item->canBeEdited()) {
            return ['success' => false, 'message' => 'This item cannot be edited in its current status'];
        }

        $item->update($data);
        $item->load(['category', 'budgetCodeRelation', 'stockCodeRelation', 'workplan']);

        if ($item->workplan) {
            $item->workplan->updateBudgetFromItems();
        }

        $this->logService->create(
            'Budget item updated.',
            ['class' => __CLASS__, 'function' => __FUNCTION__, 'item_id' => $item->id, 'user_id' => Auth::id()],
            'info'
        );

        return ['success' => true, 'message' => 'Item updated successfully', 'data' => $item];
    }

    public function deleteItem(int $itemId): array
    {
        $item = WorkplanBudgetItem::findOrFail($itemId);

        if (! $item->canBeEdited()) {
            return ['success' => false, 'message' => 'This item cannot be deleted in its current status'];
        }

        $workplan = $item->workplan;
        $item->delete();

        if ($workplan) {
            $workplan->updateBudgetFromItems();
        }

        $this->logService->create(
            'Budget item deleted.',
            ['class' => __CLASS__, 'function' => __FUNCTION__, 'item_id' => $itemId, 'user_id' => Auth::id()],
            'info'
        );

        return ['success' => true, 'message' => 'Budget item deleted successfully'];
    }

    public function createItemForWorkplan(int $workplanId, array $data): array
    {
        $data['kpi_workplan_id'] = $workplanId;
        $data['status']          = 'draft';
        $data['sort_order']      = (WorkplanBudgetItem::where('kpi_workplan_id', $workplanId)
            ->where('budget_category_id', $data['budget_category_id'])
            ->max('sort_order') ?? 0) + 1;

        $item = WorkplanBudgetItem::create($data);
        $item->load(['category', 'budgetCodeRelation', 'stockCodeRelation']);

        $workplan = KPIWorkPlan::find($workplanId);
        if ($workplan) {
            $workplan->updateBudgetFromItems();
        }

        $this->logService->create(
            'Budget item created for workplan.',
            ['class' => __CLASS__, 'function' => __FUNCTION__, 'item_id' => $item->id, 'workplan_id' => $workplanId, 'user_id' => Auth::id()],
            'info'
        );

        return ['success' => true, 'message' => 'Budget item created successfully', 'data' => $item];
    }

    public function updateItemForWorkplan(int $workplanId, int $itemId, array $data): array
    {
        $item = WorkplanBudgetItem::where('kpi_workplan_id', $workplanId)->findOrFail($itemId);

        if (! $item->canBeEdited()) {
            return ['success' => false, 'message' => 'This item cannot be edited because it has been approved'];
        }

        $item->update($data);
        $item->load(['category', 'budgetCodeRelation', 'stockCodeRelation']);

        $workplan = KPIWorkPlan::find($workplanId);
        if ($workplan) {
            $workplan->updateBudgetFromItems();
        }

        $this->logService->create(
            'Budget item updated for workplan.',
            ['class' => __CLASS__, 'function' => __FUNCTION__, 'item_id' => $item->id, 'workplan_id' => $workplanId, 'user_id' => Auth::id()],
            'info'
        );

        return ['success' => true, 'message' => 'Budget item updated successfully', 'data' => $item];
    }

    public function deleteItemForWorkplan(int $workplanId, int $itemId): array
    {
        $item = WorkplanBudgetItem::where('kpi_workplan_id', $workplanId)->findOrFail($itemId);

        if (! $item->canBeEdited()) {
            return ['success' => false, 'message' => 'This item cannot be deleted because it has been approved'];
        }

        $item->delete();

        $workplan = KPIWorkPlan::find($workplanId);
        if ($workplan) {
            $workplan->updateBudgetTotal();
        }

        $this->logService->create(
            'Budget item deleted for workplan.',
            ['class' => __CLASS__, 'function' => __FUNCTION__, 'item_id' => $itemId, 'workplan_id' => $workplanId, 'user_id' => Auth::id()],
            'info'
        );

        return ['success' => true, 'message' => 'Budget item deleted successfully'];
    }
}
