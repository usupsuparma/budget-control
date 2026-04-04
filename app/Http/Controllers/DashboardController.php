<?php

namespace App\Http\Controllers;

use App\Models\CompanyPolicy;
use App\Models\Notification;
use App\Services\DashboardService\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService)
    {
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Pages
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $title         = 'Dashboard';
        $notifications = $this->getNotifications();

        return view('pages.dashboard', compact('title', 'notifications'));
    }

    public function executive(Request $request)
    {
        $title         = 'Dashboard Executive';
        $notifications = $this->getNotifications();

        $policies = CompanyPolicy::with('details')
            ->withCount('details')
            ->orderByDesc('tahun')
            ->orderBy('nama_dokumen')
            ->get();

        return view('pages.dash-executive', compact('title', 'policies', 'notifications'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AJAX — Dashboard Stats
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /dashboard/stats?year=YYYY
     * Returns top-level KPI metrics (budget total, realisasi, balance, KPI, pending activities).
     */
    public function getDashboardStats(Request $request): JsonResponse
    {
        try {
            $year   = (int) $request->query('year', now()->year);
            $data   = $this->dashboardService->getBudgetSummary($year);

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('DashboardController@getDashboardStats: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    /**
     * GET /dashboard/division-realization?year=YYYY
     * Returns budget vs realization per division.
     */
    public function getDivisionRealizationData(Request $request): JsonResponse
    {
        try {
            $year   = (int) $request->query('year', now()->year);
            $data   = $this->dashboardService->getDivisionRealizationList($year);

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('DashboardController@getDivisionRealizationData: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    /**
     * GET /dashboard/monthly-chart?year=YYYY
     * Returns Jan–Dec budget vs realization series for the chart.
     */
    public function getMonthlyChartData(Request $request): JsonResponse
    {
        try {
            $year   = (int) $request->query('year', now()->year);
            $data   = $this->dashboardService->getMonthlyBudgetVsRealization($year);

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('DashboardController@getMonthlyChartData: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Legacy AJAX endpoints (kept for backward-compat)
    // ─────────────────────────────────────────────────────────────────────────

    public function executivePoliciesByYear(Request $request): JsonResponse
    {
        $year   = (int) $request->query('year');
        $policy = CompanyPolicy::with('details')
            ->where('tahun', $year)
            ->orderBy('nama_dokumen')
            ->first();

        $html = view('pages.dash-executive-ajax', compact('policy'))->render();

        return response()->json([
            'status' => 'success',
            'year'   => $year,
            'html'   => $html,
        ]);
    }

    public function budgetSummaryByYear(Request $request): JsonResponse
    {
        $year = (int) $request->query('year');

        $row = \App\Models\WorkplanBudgetItem::query()
            ->selectRaw('kpi_workplans.year as year, SUM(workplan_budget_items.total) as total_sum')
            ->join('kpi_workplans', 'kpi_workplans.id', '=', 'workplan_budget_items.kpi_workplan_id')
            ->where('kpi_workplans.year', $year)
            ->whereNull('workplan_budget_items.deleted_at')
            ->groupBy('kpi_workplans.year')
            ->first();

        return response()->json([
            'status'    => 'success',
            'year'      => $year,
            'total_sum' => (float) ($row->total_sum ?? 0),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function getNotifications(): array
    {
        if (! Auth::check()) {
            return [];
        }

        $employeeId    = Auth::id();
        $notifications = Notification::with('category')
            ->where(function ($query) use ($employeeId) {
                $query->where('employee_id', $employeeId)
                      ->orWhereNull('employee_id');
            })
            ->latest()
            ->take(5)
            ->get();

        foreach ($notifications as $notification) {
            $notification->is_read = $notification->reads()
                ->where('employee_id', $employeeId)
                ->where('is_read', true)
                ->exists();
        }

        return $notifications->all();
    }
}
