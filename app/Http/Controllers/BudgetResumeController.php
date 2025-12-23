<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkplanBudgetItem;
use App\Models\BudgetCategory;
use App\Models\Division;
use App\Models\BudgetCode;
use App\Models\KPIWorkPlan;
use Illuminate\Support\Facades\DB;

class BudgetResumeController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Budget Resume';
        $year = $request->get('year', date('Y'));
        $categoryId = $request->get('category_id');
        $divisionId = $request->get('division_id');
        $budgetCode = $request->get('budget_code');

        // Get filter data
        $categories = BudgetCategory::active()->ordered()->get();
        $divisions = Division::orderBy('name')->get();
        $budgetCodes = BudgetCode::active()->orderBy('stock_code')->get();

        // Build query for budget items
        $query = WorkplanBudgetItem::with([
            'workplan.kpiDepartment.department.division',
            'workplan.kpiSection.kpiDepartment.department.division',
            'category',
            'budgetCodeRelation'
        ])
        ->whereHas('workplan', function($q) use ($year) {
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
            $query->whereHas('workplan', function($q) use ($divisionId) {
                $q->where(function($subQ) use ($divisionId) {
                    // For department KPI
                    $subQ->where('kpi_type', 'App\Models\KPIDepartment')
                        ->whereHas('kpiDepartment', function($kpiQ) use ($divisionId) {
                            $kpiQ->whereHas('department', function($deptQ) use ($divisionId) {
                                $deptQ->where('division_id', $divisionId);
                            });
                        });
                })->orWhere(function($subQ) use ($divisionId) {
                    // For section KPI
                    $subQ->where('kpi_type', 'App\Models\KPISection')
                        ->whereHas('kpiSection', function($kpiQ) use ($divisionId) {
                            $kpiQ->whereHas('kpiDepartment.department', function($deptQ) use ($divisionId) {
                                $deptQ->where('division_id', $divisionId);
                            });
                        });
                });
            });
        }

        $budgetItems = $query->get();

        // Group and organize data
        $budgetData = $this->organizeBudgetData($budgetItems);

        return view('pages.budget.budget-resume', compact(
            'title', 
            'year',
            'categories',
            'divisions',
            'budgetCodes',
            'budgetData',
            'categoryId',
            'divisionId',
            'budgetCode'
        ));
    }

    private function organizeBudgetData($budgetItems)
    {
        $organized = [];

        foreach ($budgetItems as $item) {
            $workplan = $item->workplan;
            
            // Determine division based on kpi_type
            $divisionName = 'N/A';
            if ($workplan->kpi_type === 'department' && $workplan->kpiDepartment) {
                $divisionName = $workplan->kpiDepartment->department->division->name ?? 'N/A';
            } elseif ($workplan->kpi_type === 'section' && $workplan->kpiSection) {
                $divisionName = $workplan->kpiSection->kpiDepartment->department->division->name ?? 'N/A';
            }

            $categoryName = $item->category->name ?? 'Uncategorized';
            
            // Initialize structure - group by division only
            if (!isset($organized[$divisionName])) {
                $organized[$divisionName] = [];
            }

            $organized[$divisionName][] = [
                'category_name' => $categoryName,
                'budget_code' => $item->budget_code ?? '-',
                'budget_name' => $item->description ?? $workplan->activity ?? '-',
                'total' => $item->total ?? 0,
                'months' => [
                    'jan' => $item->activity_jan ?? 0,
                    'feb' => $item->activity_feb ?? 0,
                    'mar' => $item->activity_mar ?? 0,
                    'apr' => $item->activity_apr ?? 0,
                    'may' => $item->activity_may ?? 0,
                    'jun' => $item->activity_jun ?? 0,
                    'jul' => $item->activity_jul ?? 0,
                    'aug' => $item->activity_aug ?? 0,
                    'sep' => $item->activity_sep ?? 0,
                    'oct' => $item->activity_oct ?? 0,
                    'nov' => $item->activity_nov ?? 0,
                    'dec' => $item->activity_dec ?? 0,
                ],
            ];
        }

        return $organized;
    }
}
