<?php

namespace App\Services\DashboardService;

/**
 * Service interface for Dashboard analytics.
 *
 * All business logic for dashboard metrics must reside here,
 * NOT in the DashboardController.
 */
interface DashboardService
{
    /**
     * Get top-level budget summary for the given year.
     *
     * Calculation source: budget_mutations ledger (immutable source of truth).
     * - total_budget   : SUM of CREDIT mutations with category INITIAL_BUDGET
     * - total_realization: SUM of DEBIT mutations (CASH_ADVANCE + LPJ_REIMBURSE)
     * - balance        : total_budget - total_realization
     * - kpi_achievement: hardcoded 0 (pending KPI integration)
     *
     * @param  int  $year  e.g. 2025
     * @return array{
     *     total_budget: float,
     *     total_realization: float,
     *     balance: float,
     *     kpi_achievement: float,
     *     pending_activities: int,
     * }
     */
    public function getBudgetSummary(int $year): array;

    /**
     * Get budget realization breakdown per division for the given year.
     *
     * Chain: BudgetMutation → WorkplanBudgetItem → KpiWorkplan
     *        → (KPIDepartement|KpiSection) → KpiDivision → Division
     *
     * @param  int  $year
     * @return array<int, array{
     *     division_id: int,
     *     division_name: string,
     *     budget: float,
     *     realization: float,
     *     percentage: float,
     * }>
     */
    public function getDivisionRealizationList(int $year): array;

    /**
     * Get monthly budget vs realization data for the given year (Jan–Dec).
     *
     * - budget_per_month      : INITIAL_BUDGET credits grouped by month of created_at
     * - realization_per_month : DEBIT mutations (CASH_ADVANCE+LPJ_REIMBURSE) grouped by month of created_at
     *
     * @param  int  $year
     * @return array{
     *     labels: string[],
     *     budget: float[],
     *     realization: float[],
     * }
     */
    public function getMonthlyBudgetVsRealization(int $year): array;
}
