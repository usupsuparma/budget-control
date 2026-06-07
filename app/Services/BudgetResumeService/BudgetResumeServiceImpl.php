<?php

namespace App\Services\BudgetResumeService;

use App\Models\BudgetCategory;
use App\Models\BudgetCode;
use App\Models\BudgetMutation;
use App\Models\Division;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BudgetResumeServiceImpl implements BudgetResumeService
{
    private const MONTHS = [
        1 => 'JAN',
        2 => 'FEB',
        3 => 'MAR',
        4 => 'APR',
        5 => 'MAY',
        6 => 'JUN',
        7 => 'JUL',
        8 => 'AUG',
        9 => 'SEP',
        10 => 'OCT',
        11 => 'NOV',
        12 => 'DEC',
    ];

    public function getPageData(array $filters = []): array
    {
        $year = (int) ($filters['year'] ?? date('Y'));
        $categoryId = $filters['category_id'] ?? null;
        $divisionId = $filters['division_id'] ?? null;
        $budgetCode = $filters['budget_code'] ?? null;

        $budgetRows = $this->getLedgerBudgetRows($year, $categoryId, $divisionId, $budgetCode);
        $budgetItemIds = $budgetRows->pluck('item_id')->all();
        $budgetMonths = $this->getLedgerMonthsForItems($budgetItemIds, 'budget');
        $realizationMonths = $this->getLedgerMonthsForItems($budgetItemIds, 'realization');
        $pendingSubmissions = $this->getPendingSubmissions($budgetItemIds, $year);
        $budgetData = $this->organizeBudgetData($budgetRows, $budgetMonths, $realizationMonths, $pendingSubmissions);

        return [
            'title' => 'Budget Resume',
            'year' => $year,
            'years' => range((int) date('Y') + 2, (int) date('Y') - 5),
            'categories' => BudgetCategory::active()->ordered()->get(),
            'divisions' => Division::orderBy('name')->get(),
            'budgetCodes' => BudgetCode::active()->orderBy('budget_code')->get(),
            'budgetData' => $budgetData,
            'summary' => $this->buildSummary($budgetData),
            'categoryId' => $categoryId,
            'divisionId' => $divisionId,
            'budgetCode' => $budgetCode,
        ];
    }

    private function getLedgerBudgetRows(int $year, mixed $categoryId, mixed $divisionId, mixed $budgetCode): Collection
    {
        $query = DB::table('workplan_budget_items as wbi')
            ->join('kpi_workplans as kw', 'kw.id', '=', 'wbi.kpi_workplan_id')
            ->join('budget_mutations as bm', 'bm.workplan_budget_item_id', '=', 'wbi.id')
            ->leftJoin('budget_categories as bc', 'bc.id', '=', 'wbi.budget_category_id')
            ->leftJoin('kpi_department as kd', function ($join) {
                $join->on('kw.kpi_id', '=', 'kd.id')
                    ->where('kw.kpi_type', '=', 'department');
            })
            ->leftJoin('kpi_section as ks', function ($join) {
                $join->on('kw.kpi_id', '=', 'ks.id')
                    ->where('kw.kpi_type', '=', 'section');
            })
            ->leftJoin('kpi_department as kd_section', 'kd_section.id', '=', 'ks.kpi_department_id')
            ->leftJoin('kpi_division as kdiv', function ($join) {
                $join->on(
                    DB::raw("CASE WHEN kw.kpi_type = 'department' THEN kd.kpi_division_id ELSE kd_section.kpi_division_id END"),
                    '=',
                    'kdiv.id'
                );
            })
            ->leftJoin('division as d', 'd.id', '=', 'kdiv.division_id')
            ->where('kw.year', $year)
            ->whereNull('wbi.deleted_at')
            ->whereNull('kw.deleted_at');

        if ($categoryId && $categoryId !== 'all') {
            $query->where('wbi.budget_category_id', (int) $categoryId);
        }

        if ($budgetCode && $budgetCode !== 'all') {
            $query->where('wbi.budget_code', $budgetCode);
        }

        if ($divisionId && $divisionId !== 'all') {
            $query->where('kdiv.division_id', (int) $divisionId);
        }

        return $query
            ->selectRaw("
                wbi.id AS item_id,
                COALESCE(d.name, 'N/A') AS division_name,
                COALESCE(bc.name, 'Uncategorized') AS category_name,
                COALESCE(wbi.budget_code, '-') AS budget_code,
                COALESCE(wbi.description, kw.activity, '-') AS budget_name,
                COALESCE(SUM(CASE
                    WHEN bm.mutation_type = 'C' AND bm.category IN ('INITIAL_BUDGET', 'BUDGET_AMENDMENT', 'BUDGET_RELOCATION_IN') THEN bm.amount
                    WHEN bm.mutation_type = 'D' AND bm.category IN ('BUDGET_AMENDMENT', 'BUDGET_RELOCATION_OUT') THEN -bm.amount
                    ELSE 0
                END), 0) AS budget_amount,
                COALESCE(SUM(CASE
                    WHEN bm.mutation_type = 'D' AND bm.category IN ('CASH_ADVANCE', 'LPJ_REIMBURSE') THEN bm.amount
                    WHEN bm.mutation_type = 'C' AND bm.category = 'LPJ_REFUND' THEN -bm.amount
                    ELSE 0
                END), 0) AS realization_amount,
                COALESCE(SUM(CASE WHEN bm.mutation_type = 'C' THEN bm.amount ELSE -bm.amount END), 0) AS ledger_balance
            ")
            ->groupBy('wbi.id', 'd.name', 'bc.name', 'wbi.budget_code', 'wbi.description', 'kw.activity')
            ->orderBy('d.name')
            ->orderBy('bc.name')
            ->orderBy('wbi.budget_code')
            ->get();
    }

    private function getLedgerMonthsForItems(array $budgetItemIds, string $type): Collection
    {
        if (empty($budgetItemIds)) {
            return collect();
        }

        $caseExpression = $type === 'budget'
            ? "CASE
                WHEN mutation_type = 'C' AND category IN ('INITIAL_BUDGET', 'BUDGET_AMENDMENT', 'BUDGET_RELOCATION_IN') THEN amount
                WHEN mutation_type = 'D' AND category IN ('BUDGET_AMENDMENT', 'BUDGET_RELOCATION_OUT') THEN -amount
                ELSE 0
            END"
            : "CASE
                WHEN mutation_type = 'D' AND category IN ('CASH_ADVANCE', 'LPJ_REIMBURSE') THEN amount
                WHEN mutation_type = 'C' AND category = 'LPJ_REFUND' THEN -amount
                ELSE 0
            END";

        return BudgetMutation::query()
            ->whereIn('workplan_budget_item_id', $budgetItemIds)
            ->selectRaw('workplan_budget_item_id, MONTH(created_at) AS month, COALESCE(SUM(' . $caseExpression . '), 0) AS amount')
            ->groupByRaw('workplan_budget_item_id, MONTH(created_at)')
            ->get()
            ->groupBy('workplan_budget_item_id');
    }

    private function getPendingSubmissions(array $budgetItemIds, int $year): Collection
    {
        if (empty($budgetItemIds)) {
            return collect();
        }

        return DB::table('transaction_details as td')
            ->join('transactions as t', 't.id', '=', 'td.transaction_id')
            ->whereIn('td.budget_id', $budgetItemIds)
            ->whereIn('t.status_approval', [
                Transaction::APPROVAL_STATUS_PENDING,
                Transaction::APPROVAL_STATUS_IN_PROGRESS,
            ])
            ->where('t.status', Transaction::STATUS_PROGRESS)
            ->whereYear('t.transaction_date', $year)
            ->whereNull('t.deleted_at')
            ->whereNull('td.deleted_at')
            ->selectRaw('td.budget_id, MONTH(t.transaction_date) AS month, COALESCE(SUM(td.estimated_total), 0) AS amount')
            ->groupBy('td.budget_id', 'month')
            ->get()
            ->groupBy('budget_id');
    }

    private function organizeBudgetData(
        Collection $budgetRows,
        Collection $budgetMonths,
        Collection $realizationMonths,
        Collection $pendingSubmissions
    ): array
    {
        $organized = [];

        foreach ($budgetRows as $row) {
            $itemBudgetMonths = $this->normalizeMonths(
                ($budgetMonths[$row->item_id] ?? collect())->pluck('amount', 'month')
            );
            $itemRealizationMonths = $this->normalizeMonths(
                ($realizationMonths[$row->item_id] ?? collect())->pluck('amount', 'month')
            );
            $submissionMonths = $this->normalizeMonths(
                ($pendingSubmissions[$row->item_id] ?? collect())->pluck('amount', 'month')
            );
            $totalSubmission = array_sum($submissionMonths);

            $months = [];
            foreach (self::MONTHS as $monthName) {
                $months[$monthName] = [
                    'budget' => $itemBudgetMonths[$monthName] ?? 0.0,
                    'realization' => $itemRealizationMonths[$monthName] ?? 0.0,
                    'submission' => $submissionMonths[$monthName] ?? 0.0,
                    'balance' => ($itemBudgetMonths[$monthName] ?? 0.0)
                        - ($itemRealizationMonths[$monthName] ?? 0.0)
                        - ($submissionMonths[$monthName] ?? 0.0),
                ];
            }

            $divisionName = $row->division_name ?: 'N/A';
            $organized[$divisionName][] = [
                'category_name' => $row->category_name,
                'budget_code' => $row->budget_code,
                'budget_name' => $row->budget_name,
                'total' => (float) $row->budget_amount,
                'realization' => (float) $row->realization_amount,
                'total_submission' => (float) $totalSubmission,
                'balance' => (float) $row->ledger_balance - (float) $totalSubmission,
                'months' => $months,
            ];
        }

        return $organized;
    }

    private function normalizeMonths(iterable $monthlyValues): array
    {
        $months = array_fill_keys(array_values(self::MONTHS), 0.0);

        foreach ($monthlyValues as $month => $amount) {
            $monthName = self::MONTHS[(int) $month] ?? null;

            if ($monthName) {
                $months[$monthName] = (float) $amount;
            }
        }

        return $months;
    }

    private function buildSummary(array $budgetData): array
    {
        $summary = [
            'total_budget' => 0.0,
            'total_realization' => 0.0,
            'total_submission' => 0.0,
            'total_balance' => 0.0,
        ];

        foreach ($budgetData as $items) {
            foreach ($items as $item) {
                $summary['total_budget'] += (float) $item['total'];
                $summary['total_realization'] += (float) $item['realization'];
                $summary['total_submission'] += (float) $item['total_submission'];
                $summary['total_balance'] += (float) $item['balance'];
            }
        }

        return $summary;
    }
}
