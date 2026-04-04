<?php

namespace App\Services\DashboardService;

use App\Models\BudgetMutation;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DashboardServiceImpl implements DashboardService
{
    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * {@inheritdoc}
     */
    public function getBudgetSummary(int $year): array
    {
        // Total Budget = SUM of all INITIAL_BUDGET CREDIT mutations for items in the given year
        $totalBudget = (float) DB::table('budget_mutations as bm')
            ->join('workplan_budget_items as wbi', 'wbi.id', '=', 'bm.workplan_budget_item_id')
            ->join('kpi_workplans as kw', 'kw.id', '=', 'wbi.kpi_workplan_id')
            ->where('kw.year', $year)
            ->where('bm.mutation_type', BudgetMutation::TYPE_CREDIT)
            ->where('bm.category', BudgetMutation::CATEGORY_INITIAL_BUDGET)
            ->whereNull('wbi.deleted_at')
            ->whereNull('kw.deleted_at')
            ->sum('bm.amount');

        // Total Realisasi = SUM of all DEBIT mutations (CASH_ADVANCE + LPJ_REIMBURSE) for items in the given year
        $totalRealization = (float) DB::table('budget_mutations as bm')
            ->join('workplan_budget_items as wbi', 'wbi.id', '=', 'bm.workplan_budget_item_id')
            ->join('kpi_workplans as kw', 'kw.id', '=', 'wbi.kpi_workplan_id')
            ->where('kw.year', $year)
            ->where('bm.mutation_type', BudgetMutation::TYPE_DEBIT)
            ->whereIn('bm.category', [
                BudgetMutation::CATEGORY_CASH_ADVANCE,
                BudgetMutation::CATEGORY_LPJ_REIMBURSE,
            ])
            ->whereNull('wbi.deleted_at')
            ->whereNull('kw.deleted_at')
            ->sum('bm.amount');

        // Pending Activities = transactions waiting for approval
        $pendingActivities = Transaction::where('status_approval', Transaction::APPROVAL_STATUS_PENDING)
            ->whereYear('transaction_date', $year)
            ->count();

        return [
            'total_budget'       => $totalBudget,
            'total_realization'  => $totalRealization,
            'balance'            => $totalBudget - $totalRealization,
            'kpi_achievement'    => 0.0,   // Placeholder – pending KPI service integration
            'pending_activities' => $pendingActivities,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDivisionRealizationList(int $year): array
    {
        /*
         * Strategy: use raw SQL join to traverse the full chain in one query.
         *
         * Chain for kpi_type = 'department':
         *   wbi → kpi_workplans (kw) → kpi_department (kd) → kpi_division (kdiv) → division (d)
         *
         * Chain for kpi_type = 'section':
         *   wbi → kpi_workplans (kw) → kpi_section (ks) → kpi_department (kd) → kpi_division (kdiv) → division (d)
         *
         * We resolve the division_id with a CASE expression inside a subquery.
         */

        $budgetRows = DB::table('budget_mutations as bm')
            ->join('workplan_budget_items as wbi', 'wbi.id', '=', 'bm.workplan_budget_item_id')
            ->join('kpi_workplans as kw', 'kw.id', '=', 'wbi.kpi_workplan_id')
            ->leftJoin('kpi_department as kd', function ($join) {
                $join->on('kw.kpi_id', '=', 'kd.id')
                     ->where('kw.kpi_type', '=', 'department');
            })
            ->leftJoin('kpi_section as ks', function ($join) {
                $join->on('kw.kpi_id', '=', 'ks.id')
                     ->where('kw.kpi_type', '=', 'section');
            })
            ->leftJoin('kpi_department as kd2', 'kd2.id', '=', 'ks.kpi_department_id')
            ->leftJoin('kpi_division as kdiv', function ($join) {
                $join->on(DB::raw('CASE WHEN kw.kpi_type = \'department\' THEN kd.kpi_division_id ELSE kd2.kpi_division_id END'), '=', 'kdiv.id');
            })
            ->leftJoin('division as d', 'd.id', '=', 'kdiv.division_id')
            ->where('kw.year', $year)
            ->whereNull('wbi.deleted_at')
            ->whereNull('kw.deleted_at')
            ->whereIn('bm.mutation_type', [BudgetMutation::TYPE_CREDIT, BudgetMutation::TYPE_DEBIT])
            ->selectRaw("
                d.id as division_id,
                d.name as division_name,
                SUM(CASE WHEN bm.mutation_type = 'C' AND bm.category = 'INITIAL_BUDGET' THEN bm.amount ELSE 0 END) as budget,
                SUM(CASE WHEN bm.mutation_type = 'D' AND bm.category IN ('CASH_ADVANCE','LPJ_REIMBURSE') THEN bm.amount ELSE 0 END) as realization
            ")
            ->groupBy('d.id', 'd.name')
            ->orderBy('d.name')
            ->get();

        $result = [];
        foreach ($budgetRows as $row) {
            $budget      = (float) $row->budget;
            $realization = (float) $row->realization;
            $percentage  = $budget > 0
                ? round(($realization / $budget) * 100, 2)
                : 0.0;

            $result[] = [
                'division_id'   => $row->division_id,
                'division_name' => $row->division_name ?? '—',
                'budget'        => $budget,
                'realization'   => $realization,
                'percentage'    => $percentage,
            ];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getMonthlyBudgetVsRealization(int $year): array
    {
        $months = [
            1  => 'Jan', 2  => 'Feb', 3  => 'Mar',
            4  => 'Apr', 5  => 'May', 6  => 'Jun',
            7  => 'Jul', 8  => 'Aug', 9  => 'Sep',
            10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
        ];

        // Budget per month — INITIAL_BUDGET credits, grouped by month(created_at)
        $budgetData = DB::table('budget_mutations as bm')
            ->join('workplan_budget_items as wbi', 'wbi.id', '=', 'bm.workplan_budget_item_id')
            ->join('kpi_workplans as kw', 'kw.id', '=', 'wbi.kpi_workplan_id')
            ->where('kw.year', $year)
            ->where('bm.mutation_type', BudgetMutation::TYPE_CREDIT)
            ->where('bm.category', BudgetMutation::CATEGORY_INITIAL_BUDGET)
            ->whereNull('wbi.deleted_at')
            ->whereNull('kw.deleted_at')
            ->selectRaw('MONTH(bm.created_at) as month, SUM(bm.amount) as total')
            ->groupByRaw('MONTH(bm.created_at)')
            ->pluck('total', 'month')
            ->toArray();

        // Realization per month — DEBIT mutations grouped by month(created_at) 
        $realizationData = DB::table('budget_mutations as bm')
            ->join('workplan_budget_items as wbi', 'wbi.id', '=', 'bm.workplan_budget_item_id')
            ->join('kpi_workplans as kw', 'kw.id', '=', 'wbi.kpi_workplan_id')
            ->where('kw.year', $year)
            ->where('bm.mutation_type', BudgetMutation::TYPE_DEBIT)
            ->whereIn('bm.category', [
                BudgetMutation::CATEGORY_CASH_ADVANCE,
                BudgetMutation::CATEGORY_LPJ_REIMBURSE,
            ])
            ->whereNull('wbi.deleted_at')
            ->whereNull('kw.deleted_at')
            ->selectRaw('MONTH(bm.created_at) as month, SUM(bm.amount) as total')
            ->groupByRaw('MONTH(bm.created_at)')
            ->pluck('total', 'month')
            ->toArray();

        $labels       = [];
        $budgetSeries = [];
        $realSeries   = [];

        foreach ($months as $num => $label) {
            $labels[]       = $label;
            $budgetSeries[] = (float) ($budgetData[$num] ?? 0);
            $realSeries[]   = (float) ($realizationData[$num] ?? 0);
        }

        return [
            'labels'      => $labels,
            'budget'      => $budgetSeries,
            'realization' => $realSeries,
        ];
    }
}
