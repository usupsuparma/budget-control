<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Director;
use App\Models\Division;
use App\Models\Department;
use App\Models\Section;
use App\Models\KPIDivision;
use App\Models\KPIDepartment;
use App\Models\KPISection;
use App\Models\KPIWorkPlan;
use App\Models\WorkplanBudgetItem;
use Illuminate\Support\Facades\Log;

class BudgetAdminController extends Controller
{
    public function index(Request $request)
    {
        $years = range(date('Y'), date('Y') - 5);
        return view('pages.budget.budget-admin', compact('years'));
    }

    public function getBudgetData(Request $request)
    {
        try {
            //code...

            $year = $request->input('year', date('Y'));

            $data = [];
            $no = 1;

            // Get all Directors
            $directors = Director::all();

            foreach ($directors as $director) {
                // Add Director Row
                $directorRow = [
                    'id' => 'director-' . $director->id,
                    'no' => $no++,
                    'structure' => strtoupper($director->name),
                    'type' => 'director',
                    'director_id' => $director->id,
                    'company_policy_url' => null,
                    'kpi_url' => null,
                    'workplan_url' => null,
                    'plan_budget_url' => null,
                    'budget_url' => null,
                    'has_kpi' => false,
                    'level' => 0
                ];
                $data[] = $directorRow;

                // Get Divisions under this Director
                $divisions = Division::where('director_id', $director->id)->get();

                foreach ($divisions as $division) {
                    // Check if division has KPI
                    $kpiDivision = KPIDivision::where('division_id', $division->id)
                        ->where('year', $year)
                        ->first();

                    $hasKpi = !is_null($kpiDivision);

                    $divisionRow = [
                        'id' => 'division-' . $division->id,
                        'no' => $no++,
                        'structure' => $division->code . ' ' . strtoupper($division->name),
                        'type' => 'division',
                        'division_id' => $division->id,
                        'company_policy_url' => $hasKpi ? route('company-policy.index') : null,
                        'kpi_url' => $hasKpi ? route('kpidivision.index', ['division_id' => $division->id, 'year' => $year]) : null,
                        'workplan_url' => $hasKpi ? route('workplan.index', ['division_id' => $division->id, 'year' => $year]) : null,
                        'plan_budget_url' => $hasKpi ? route('budget-user.index', ['division_id' => $division->id, 'year' => $year]) : null,
                        'budget_url' => route('anggaran.index', ['division_id' => $division->id, 'year' => $year]),
                        'has_kpi' => $hasKpi,
                        'level' => 1
                    ];
                    $data[] = $divisionRow;

                    // Get Departments under this Division
                    $departments = Department::where('division_id', $division->id)->get();

                    foreach ($departments as $department) {
                        // Check if department has KPI
                        $kpiDepartment = KPIDepartment::whereHas('kpiDivision', function ($q) use ($division, $year) {
                            $q->where('division_id', $division->id)
                                ->where('year', $year);
                        })
                            ->where('department_id', $department->id)
                            ->first();

                        $hasDeptKpi = !is_null($kpiDepartment);

                        $departmentRow = [
                            'id' => 'department-' . $department->id,
                            'no' => $no++,
                            'structure' => $department->code . ' ' . strtoupper($department->name),
                            'type' => 'department',
                            'department_id' => $department->id,
                            'company_policy_url' => $hasDeptKpi ? route('company-policy.index') : null,
                            'kpi_url' => $hasDeptKpi ? route('kpidepartment.index', ['department_id' => $department->id, 'year' => $year]) : null,
                            'workplan_url' => $hasDeptKpi ? route('workplan.index', ['division_id' => $division->id, 'year' => $year]) : null,
                            'plan_budget_url' => $hasDeptKpi ? route('budget-user.index', ['division_id' => $division->id, 'year' => $year]) : null,
                            'budget_url' => route('anggaran.index', ['department_id' => $department->id, 'year' => $year]),
                            'has_kpi' => $hasDeptKpi,
                            'level' => 2
                        ];
                        $data[] = $departmentRow;

                        // Get Sections under this Department
                        $sections = Section::where('department_id', $department->id)->get();

                        foreach ($sections as $section) {
                            // Check if section has KPI
                            $kpiSection = KPISection::whereHas('kpiDepartment.kpiDivision', function ($q) use ($division, $year) {
                                $q->where('division_id', $division->id)
                                    ->where('year', $year);
                            })
                                ->where('section_id', $section->id)
                                ->first();

                            $hasSectionKpi = !is_null($kpiSection);

                            $sectionRow = [
                                'id' => 'section-' . $section->id,
                                'no' => $no++,
                                'structure' => $section->code . ' ' . strtoupper($section->name),
                                'type' => 'section',
                                'section_id' => $section->id,
                                'company_policy_url' => $hasSectionKpi ? route('company-policy.index') : null,
                                'kpi_url' => $hasSectionKpi ? route('kpisection.index', ['section_id' => $section->id, 'year' => $year]) : null,
                                'workplan_url' => $hasSectionKpi ? route('workplan.index', ['division_id' => $division->id, 'year' => $year]) : null,
                                'plan_budget_url' => $hasSectionKpi ? route('budget-user.index', ['division_id' => $division->id, 'year' => $year]) : null,
                                'budget_url' => route('anggaran.index', ['section_id' => $section->id, 'year' => $year]),
                                'has_kpi' => $hasSectionKpi,
                                'level' => 3
                            ];
                            $data[] = $sectionRow;
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error("Error fetching budget admin data: " . $th->getMessage(),["BudgetAdminController","getBudgetData"]);
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data: ' . $th->getMessage()
            ], 500);
        }
    }
}
