<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkplanBudgetItem;
use App\Models\BudgetCategory;
use App\Models\Division;
use App\Models\BudgetCode;
use App\Models\KPIWorkPlan;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;

class BudgetResumeController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Budget Resume';
        $year = $request->get('year', date('Y'));

        $years = range(date('Y') + 2, date('Y') - 5);
        $categoryId = $request->get('category_id');
        $divisionId = $request->get('division_id');
        $budgetCode = $request->get('budget_code');

        // Get filter data
        $categories = BudgetCategory::active()->ordered()->get();
        $divisions = Division::orderBy('name')->get();
        $budgetCodes = BudgetCode::active()->get();

        // Build query for budget items
        $query = WorkplanBudgetItem::with([
            'workplan.KPIDepartment.department.division',
            'workplan.kpiSection.KPIDepartment.department.division',
            'category',
            'budgetCodeRelation'
        ])
            ->whereHas('workplan', function ($q) use ($year) {
                $q->where('year', $year);
            });

        // Apply filters
        if ($categoryId && $categoryId !== 'all') {
            $query->where('budget_category_id', $categoryId);
        }

        if ($budgetCode && $budgetCode !== 'all') {
            $query->where('budget_code', $budgetCode);
        }

        if ($divisionId && $divisionId !== 'all') {
            $query->whereHas('workplan', function ($q) use ($divisionId) {
                $q->where(function ($subQ) use ($divisionId) {
                    // For department KPI
                    $subQ->where('kpi_type', 'App\Models\KPIDepartment')
                        ->whereHas('KPIDepartment', function ($kpiQ) use ($divisionId) {
                            $kpiQ->whereHas('department', function ($deptQ) use ($divisionId) {
                                $deptQ->where('division_id', $divisionId);
                            });
                        });
                })->orWhere(function ($subQ) use ($divisionId) {
                    // For section KPI
                    $subQ->where('kpi_type', 'App\Models\KPISection')
                        ->whereHas('kpiSection', function ($kpiQ) use ($divisionId) {
                        $kpiQ->whereHas('KPIDepartment.department', function ($deptQ) use ($divisionId) {
                                $deptQ->where('division_id', $divisionId);
                            });
                        });
                });
            });
        }

        $budgetItems = $query->get();

        // Fetch Submissions (Transactions with status 3 or 4)
        $budgetItemIds = $budgetItems->pluck('id');
        $submissions = TransactionDetail::join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereIn('transaction_details.budget_id', $budgetItemIds)
            ->whereIn('transactions.status', [Transaction::STATUS_PAID, Transaction::STATUS_COMPLETED])
            ->whereYear('transactions.transaction_date', $year)
            ->whereNull('transactions.deleted_at')
            ->whereNull('transaction_details.deleted_at')
            ->select(
                'transaction_details.budget_id',
                DB::raw('MONTH(transactions.transaction_date) as month'),
                DB::raw('SUM(transaction_details.estimated_total) as total_submission')
            )
            ->groupBy('transaction_details.budget_id', 'month')
            ->get()
            ->groupBy('budget_id');

        // Group and organize data
        $budgetData = $this->organizeBudgetData($budgetItems, $submissions);

        return view('pages.budget.budget-resume', compact(
            'title',
            'year',
            'categories',
            'divisions',
            'budgetCodes',
            'budgetData',
            'categoryId',
            'divisionId',
            'budgetCode',
            'years'
        ));
    }

    private function organizeBudgetData($budgetItems, $submissions = [])
    {
        $organized = [];

        foreach ($budgetItems as $item) {
            $workplan = $item->workplan;

            // Determine division based on kpi_type
            $divisionName = 'N/A';
            if ($workplan->kpi_type === 'department' && $workplan->KPIDepartment) {
                $divisionName = $workplan->KPIDepartment->department->division->name ?? 'N/A';
            } elseif ($workplan->kpi_type === 'section' && $workplan->kpiSection) {
                $divisionName = $workplan->kpiSection->KPIDepartment->department->division->name ?? 'N/A';
            }

            $categoryName = $item->category->name ?? 'Uncategorized';

            // Initialize structure - group by division only
            if (!isset($organized[$divisionName])) {
                $organized[$divisionName] = [];
            }

            // Map submissions for this item
            $itemSubmissions = $submissions[$item->id] ?? collect();
            $submissionByMonth = [
                'JAN' => $itemSubmissions->where('month', 1)->sum('total_submission'),
                'FEB' => $itemSubmissions->where('month', 2)->sum('total_submission'),
                'MAR' => $itemSubmissions->where('month', 3)->sum('total_submission'),
                'APR' => $itemSubmissions->where('month', 4)->sum('total_submission'),
                'MAY' => $itemSubmissions->where('month', 5)->sum('total_submission'),
                'JUN' => $itemSubmissions->where('month', 6)->sum('total_submission'),
                'JUL' => $itemSubmissions->where('month', 7)->sum('total_submission'),
                'AUG' => $itemSubmissions->where('month', 8)->sum('total_submission'),
                'SEP' => $itemSubmissions->where('month', 9)->sum('total_submission'),
                'OCT' => $itemSubmissions->where('month', 10)->sum('total_submission'),
                'NOV' => $itemSubmissions->where('month', 11)->sum('total_submission'),
                'DEC' => $itemSubmissions->where('month', 12)->sum('total_submission'),
            ];
            $totalSubmission = array_sum($submissionByMonth);

            $organized[$divisionName][] = [
                'category_name' => $categoryName,
                'budget_code' => $item->budget_code ?? '-',
                'budget_name' => $item->description ?? $workplan->activity ?? '-',
                'total' => $item->total ?? 0,
                'total_submission' => $totalSubmission,
                'months' => [
                    'JAN' => $item->activity_jan ?? 0,
                    'FEB' => $item->activity_feb ?? 0,
                    'MAR' => $item->activity_mar ?? 0,
                    'APR' => $item->activity_apr ?? 0,
                    'MAY' => $item->activity_may ?? 0,
                    'JUN' => $item->activity_jun ?? 0,
                    'JUL' => $item->activity_jul ?? 0,
                    'AUG' => $item->activity_aug ?? 0,
                    'SEP' => $item->activity_sep ?? 0,
                    'OCT' => $item->activity_oct ?? 0,
                    'NOV' => $item->activity_nov ?? 0,
                    'DEC' => $item->activity_dec ?? 0,
                ],
                'submission_months' => $submissionByMonth,
            ];
        }

        return $organized;
    }
}
