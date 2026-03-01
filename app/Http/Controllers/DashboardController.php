<?php

namespace App\Http\Controllers;

use App\Models\CompanyPolicy;
use App\Models\WorkplanBudgetItem;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Dashboard';
        
        $notifications = [];
        if (Auth::check()) {
            $employeeId = Auth::id();
            $notifications = Notification::with('category')
                ->where(function($query) use ($employeeId) {
                    $query->where('employee_id', $employeeId)
                          ->orWhereNull('employee_id');
                })
                ->latest()
                ->take(5)
                ->get();
                
            // Check read status for each notification
            foreach ($notifications as $notification) {
                $notification->is_read = $notification->reads()
                    ->where('employee_id', $employeeId)
                    ->where('is_read', true)
                    ->exists();
            }
        }

        return view('pages.dashboard', compact('title', 'notifications'));
    }
    public function executive(Request $request)
    {
        $title = 'Dashboard Executive';
        $policies = CompanyPolicy::with('details')      // ambil strategic goals
            ->withCount('details')               // hitung jumlah goals
            ->orderByDesc('tahun')
            ->orderBy('nama_dokumen')
            ->get();

        $notifications = [];
        if (Auth::check()) {
            $employeeId = Auth::id();
            $notifications = Notification::with('category')
                ->where(function($query) use ($employeeId) {
                    $query->where('employee_id', $employeeId)
                          ->orWhereNull('employee_id');
                })
                ->latest()
                ->take(5)
                ->get();
                
            // Check read status for each notification
            foreach ($notifications as $notification) {
                $notification->is_read = $notification->reads()
                    ->where('employee_id', $employeeId)
                    ->where('is_read', true)
                    ->exists();
            }
        }

        return view('pages.dash-executive', compact('title','policies', 'notifications'));
    }

    public function executivePoliciesByYear(Request $request)
    {
        $year = (int) $request->query('year');

        $policy = CompanyPolicy::with('details')
            ->where('tahun', $year)
            ->orderBy('nama_dokumen')   // ambil dokumen pertama (atau yang kamu mau)
            ->first();

        $html = view('pages.dash-executive-ajax', compact('policy'))->render();

        return response()->json([
            'status' => 'success',
            'year'   => $year,
            'html'   => $html,
        ]);
    }

    public function budgetSummaryByYear(Request $request)
    {
        $year = (int) $request->query('year');

        $row = WorkplanBudgetItem::query()
            ->selectRaw('kpi_workplans.year as year, SUM(workplan_budget_items.total) as total_sum')
            ->join('kpi_workplans', 'kpi_workplans.id', '=', 'workplan_budget_items.kpi_workplan_id')
            ->where('kpi_workplans.year', $year)
            ->whereNull('workplan_budget_items.deleted_at')
            ->groupBy('kpi_workplans.year')
            ->first();

        return response()->json([
            'status' => 'success',
            'year' => $year,
            'total_sum' => (float) ($row->total_sum ?? 0),
        ]);
    }

}
