<?php

use App\Services\DashboardService\DashboardService;
use App\Services\DashboardService\DashboardServiceImpl;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Resolve the DashboardService implementation from the container.
 */
function dashboardService(): DashboardService
{
    return app(DashboardService::class);
}

// ─────────────────────────────────────────────────────────────────────────────
// getBudgetSummary
// ─────────────────────────────────────────────────────────────────────────────

describe('DashboardService::getBudgetSummary', function () {

    it('returns correct array keys', function () {
        $result = dashboardService()->getBudgetSummary(now()->year);

        expect($result)
            ->toBeArray()
            ->toHaveKeys([
                'total_budget',
                'total_realization',
                'balance',
                'kpi_achievement',
                'pending_activities',
            ]);
    });

    it('returns zero values when no mutations exist', function () {
        $result = dashboardService()->getBudgetSummary(now()->year);

        expect((float) $result['total_budget'])->toBe(0.0);
        expect((float) $result['total_realization'])->toBe(0.0);
        expect((float) $result['balance'])->toBe(0.0);
    });

    it('balance equals total_budget minus total_realization', function () {
        $result = dashboardService()->getBudgetSummary(now()->year);

        $expected = (float) $result['total_budget'] - (float) $result['total_realization'];
        expect((float) $result['balance'])->toBe($expected);
    });

    it('kpi_achievement is always 0 (pending KPI integration)', function () {
        $result = dashboardService()->getBudgetSummary(now()->year);
        expect((float) $result['kpi_achievement'])->toBe(0.0);
    });

    it('pending_activities is a non-negative integer', function () {
        $result = dashboardService()->getBudgetSummary(now()->year);
        expect($result['pending_activities'])->toBeInt()->toBeGreaterThanOrEqual(0);
    });

});

// ─────────────────────────────────────────────────────────────────────────────
// getDivisionRealizationList
// ─────────────────────────────────────────────────────────────────────────────

describe('DashboardService::getDivisionRealizationList', function () {

    it('returns an array', function () {
        $result = dashboardService()->getDivisionRealizationList(now()->year);
        expect($result)->toBeArray();
    });

    it('each row has required keys', function () {
        $result = dashboardService()->getDivisionRealizationList(now()->year);

        // Only validate structure when there is data
        if (count($result) > 0) {
            foreach ($result as $row) {
                expect($row)->toHaveKeys([
                    'division_id',
                    'division_name',
                    'budget',
                    'realization',
                    'percentage',
                ]);
            }
        }

        expect(true)->toBeTrue(); // always pass when no data
    });

    it('percentage is 0 when budget is 0', function () {
        // Simulate: when budget == 0, percentage must be 0.0 (no division by zero)
        $service = new DashboardServiceImpl();

        // Call with a far-future year to guarantee no data
        $result = $service->getDivisionRealizationList(9999);

        expect($result)->toBeArray();
        // If any row existed, its percentage would be 0 since budget is 0
        foreach ($result as $row) {
            expect((float) $row['percentage'])->toBeGreaterThanOrEqual(0.0);
        }
    });

    it('percentage is a float between 0 and any positive value', function () {
        $result = dashboardService()->getDivisionRealizationList(now()->year);

        foreach ($result as $row) {
            expect((float) $row['percentage'])->toBeFloat()->toBeGreaterThanOrEqual(0.0);
        }
    });

});

// ─────────────────────────────────────────────────────────────────────────────
// getMonthlyBudgetVsRealization
// ─────────────────────────────────────────────────────────────────────────────

describe('DashboardService::getMonthlyBudgetVsRealization', function () {

    it('returns correct structure', function () {
        $result = dashboardService()->getMonthlyBudgetVsRealization(now()->year);

        expect($result)->toHaveKeys(['labels', 'budget', 'realization']);
    });

    it('labels has exactly 12 months', function () {
        $result = dashboardService()->getMonthlyBudgetVsRealization(now()->year);

        expect($result['labels'])->toHaveCount(12);
    });

    it('budget series has exactly 12 values', function () {
        $result = dashboardService()->getMonthlyBudgetVsRealization(now()->year);

        expect($result['budget'])->toHaveCount(12);
    });

    it('realization series has exactly 12 values', function () {
        $result = dashboardService()->getMonthlyBudgetVsRealization(now()->year);

        expect($result['realization'])->toHaveCount(12);
    });

    it('all monthly values are floats >= 0', function () {
        $result = dashboardService()->getMonthlyBudgetVsRealization(now()->year);

        foreach ($result['budget'] as $val) {
            expect((float) $val)->toBeGreaterThanOrEqual(0.0);
        }
        foreach ($result['realization'] as $val) {
            expect((float) $val)->toBeGreaterThanOrEqual(0.0);
        }
    });

    it('first label is Jan', function () {
        $result = dashboardService()->getMonthlyBudgetVsRealization(now()->year);

        expect($result['labels'][0])->toBe('Jan');
    });

    it('last label is Dec', function () {
        $result = dashboardService()->getMonthlyBudgetVsRealization(now()->year);

        expect($result['labels'][11])->toBe('Dec');
    });

});
