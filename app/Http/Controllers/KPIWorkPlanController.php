<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\KPIWorkPlan;
use App\Models\KPIDivision;
use App\Models\KPIDepartment;
use App\Models\KPISection;
use App\Models\Division;
use App\Models\Section;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KPIWorkPlanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('Admin') || $user->hasRole('admin') || $user->hasRole('super-admin');

        $userDivisionId = null;
        if (!$isAdmin) {
            $employment = $user->employment;
            if ($employment) {
                $jobPosition = $employment->jobPosition;
                if ($jobPosition) {
                    $levelId = (int)$jobPosition->job_level_id;
                    $structureId = (int)$jobPosition->structure_id;

                    switch ($levelId) {
                        case 1: // Director
                            $userDivisionId = Division::where('director_id', $structureId)->first()?->id;
                            break;
                        case 2: // Division
                            $userDivisionId = $structureId;
                            break;
                        case 3: // Department
                            $userDivisionId = Department::where('id', $structureId)->first()?->division_id;
                            break;
                        default: // Section/Staff/Non-Staff
                            $section = Section::with('department')->find($structureId);
                            $userDivisionId = $section?->department?->division_id;
                            break;
                    }
                }
            }
        }

        // Get unique divisions from KPI Division (ONLY divisions that have implemented KPIs)
        $kpiDivisions = KPIDivision::with('division')
            ->select('division_id')
            ->distinct()
            ->get();

        $divisions = $kpiDivisions->map(function ($kpi) {
            return $kpi->division;
        })->filter()->unique('id')->values();

        // If non-admin and their division is not in the KPI list yet, add it so it doesn't show N/A
        if (!$isAdmin && $userDivisionId && !$divisions->contains('id', $userDivisionId)) {
            $myDiv = Division::find($userDivisionId);
            if ($myDiv) {
                $divisions->push($myDiv);
            }
        }

        // Get unique years from KPI Division
        $kpiYears = KPIDivision::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // If no KPI years exist, provide default years
        $years = !empty($kpiYears) ? $kpiYears : range(date('Y'), date('Y') + 5);

        $currentYear = date('Y');

        return view('pages.work-plan.work-plan', compact('divisions', 'years', 'isAdmin', 'userDivisionId', 'currentYear'));
    }
    /**
     * Get KPI data based on division and year
     */
    public function getKpiData(Request $request)
    {
        $divisionId = $request->division_id;
        $year = $request->year;

        if (!$divisionId || !$year) {
            return response()->json(['error' => 'Division and Year are required'], 400);
        }

        // Get KPI Division
        $kpiDivisions = KPIDivision::with(['division', 'companyPolicy'])
            ->where('division_id', $divisionId)
            ->where('year', $year)
            ->get();

        $data = [];

        foreach ($kpiDivisions as $kpiDivision) {
            $divisionData = [
                'id' => $kpiDivision->id,
                'division_goals' => $kpiDivision->division_goals,
                'target_division' => $kpiDivision->target_division,
                'departments' => []
            ];

            // Get KPI Department
            $kpiDepartments = KPIDepartment::with(['department', 'workplans' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }])
                ->where('kpi_division_id', $kpiDivision->id)
                ->get();

            foreach ($kpiDepartments as $kpiDept) {
                $deptData = [
                    'id' => $kpiDept->id,
                    'department_name' => $kpiDept->department->name ?? 'N/A',
                    'department_goals' => $kpiDept->department_goals,
                    'target_department' => $kpiDept->target_department,
                    'workplans' => $kpiDept->workplans->map(function ($wp) {
                        return $this->formatWorkplan($wp);
                    }),
                    'sections' => []
                ];

                // Get KPI Section
                $kpiSections = KPISection::with(['section', 'workplans' => function ($q) {
                    $q->orderBy('created_at', 'desc');
                }])
                    ->where('kpi_department_id', $kpiDept->id)
                    ->get();

                foreach ($kpiSections as $kpiSect) {
                    $sectData = [
                        'id' => $kpiSect->id,
                        'section_name' => $kpiSect->section->name ?? 'N/A',
                        'section_goals' => $kpiSect->section_goals,
                        'target_section' => $kpiSect->target_section,
                        'workplans' => $kpiSect->workplans->map(function ($wp) {
                            return $this->formatWorkplan($wp);
                        })
                    ];

                    $deptData['sections'][] = $sectData;
                }

                $divisionData['departments'][] = $deptData;
            }

            $data[] = $divisionData;
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Store new workplan
     */
    public function store(Request $request)
    {
        $request->validate([
            'kpi_type' => 'required|in:department,section',
            'kpi_id' => 'required|integer',
            'year' => 'required|integer',
            'activity' => 'required|string',
            'budget' => 'nullable|numeric'
        ]);

        try {
            $workplan = KPIWorkPlan::create([
                'kpi_type' => $request->kpi_type,
                'kpi_id' => $request->kpi_id,
                'year' => $request->year,
                'activity' => $request->activity,
                'duration_days' => $request->duration_days,
                'schedule_start' => $request->schedule_start,
                'schedule_end' => $request->schedule_end,
                'plan_jan' => $request->boolean('plan_jan'),
                'plan_feb' => $request->boolean('plan_feb'),
                'plan_mar' => $request->boolean('plan_mar'),
                'plan_apr' => $request->boolean('plan_apr'),
                'plan_may' => $request->boolean('plan_may'),
                'plan_jun' => $request->boolean('plan_jun'),
                'plan_jul' => $request->boolean('plan_jul'),
                'plan_aug' => $request->boolean('plan_aug'),
                'plan_sep' => $request->boolean('plan_sep'),
                'plan_oct' => $request->boolean('plan_oct'),
                'plan_nov' => $request->boolean('plan_nov'),
                'plan_dec' => $request->boolean('plan_dec'),
                'budget' => $request->budget,
                'real_jan' => $request->boolean('real_jan'),
                'real_feb' => $request->boolean('real_feb'),
                'real_mar' => $request->boolean('real_mar'),
                'real_apr' => $request->boolean('real_apr'),
                'real_may' => $request->boolean('real_may'),
                'real_jun' => $request->boolean('real_jun'),
                'real_jul' => $request->boolean('real_jul'),
                'real_aug' => $request->boolean('real_aug'),
                'real_sep' => $request->boolean('real_sep'),
                'real_oct' => $request->boolean('real_oct'),
                'real_nov' => $request->boolean('real_nov'),
                'real_dec' => $request->boolean('real_dec'),
                'description' => $request->description,
                'status' => 'draft'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Work plan created successfully',
                'data' => $this->formatWorkplan($workplan)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create work plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update workplan
     */
    public function update(Request $request, $id)
    {
        $workplan = KPIWorkPlan::findOrFail($id);

        $request->validate([
            'activity' => 'required|string',
            'budget' => 'nullable|numeric'
        ]);

        try {
            $workplan->update([
                'activity' => $request->activity,
                'duration_days' => $request->duration_days,
                'schedule_start' => $request->schedule_start,
                'schedule_end' => $request->schedule_end,
                'plan_jan' => $request->boolean('plan_jan'),
                'plan_feb' => $request->boolean('plan_feb'),
                'plan_mar' => $request->boolean('plan_mar'),
                'plan_apr' => $request->boolean('plan_apr'),
                'plan_may' => $request->boolean('plan_may'),
                'plan_jun' => $request->boolean('plan_jun'),
                'plan_jul' => $request->boolean('plan_jul'),
                'plan_aug' => $request->boolean('plan_aug'),
                'plan_sep' => $request->boolean('plan_sep'),
                'plan_oct' => $request->boolean('plan_oct'),
                'plan_nov' => $request->boolean('plan_nov'),
                'plan_dec' => $request->boolean('plan_dec'),
                'budget' => $request->budget,
                'real_jan' => $request->boolean('real_jan'),
                'real_feb' => $request->boolean('real_feb'),
                'real_mar' => $request->boolean('real_mar'),
                'real_apr' => $request->boolean('real_apr'),
                'real_may' => $request->boolean('real_may'),
                'real_jun' => $request->boolean('real_jun'),
                'real_jul' => $request->boolean('real_jul'),
                'real_aug' => $request->boolean('real_aug'),
                'real_sep' => $request->boolean('real_sep'),
                'real_oct' => $request->boolean('real_oct'),
                'real_nov' => $request->boolean('real_nov'),
                'real_dec' => $request->boolean('real_dec'),
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Work plan updated successfully',
                'data' => $this->formatWorkplan($workplan)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update work plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete workplan
     */
    public function destroy($id)
    {
        try {
            $workplan = KPIWorkPlan::findOrFail($id);
            $workplan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Work plan deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete work plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve workplan
     */
    public function approve(Request $request, $id)
    {
        try {
            $workplan = KPIWorkPlan::findOrFail($id);
            $workplan->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Work plan approved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve work plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update realization data only
     */
    public function updateRealization(Request $request, $id)
    {
        try {
            $workplan = KPIWorkPlan::findOrFail($id);

            // Update only realization fields
            $realizationData = [];
            $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

            foreach ($months as $month) {
                if ($request->has("real_{$month}")) {
                    $realizationData["real_{$month}"] = $request->boolean("real_{$month}");
                }
            }

            if (empty($realizationData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No realization data to update'
                ], 400);
            }

            $workplan->update($realizationData);

            return response()->json([
                'success' => true,
                'message' => 'Realization updated successfully',
                'data' => $this->formatWorkplan($workplan->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update realization: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format workplan data for response
     */
    private function formatWorkplan($workplan)
    {
        return [
            'id' => $workplan->id,
            'activity' => $workplan->activity,
            'duration_days' => $workplan->duration_days,
            'schedule_start' => $workplan->schedule_start?->format('Y-m-d'),
            'schedule_end' => $workplan->schedule_end?->format('Y-m-d'),
            'plan_jan' => $workplan->plan_jan,
            'plan_feb' => $workplan->plan_feb,
            'plan_mar' => $workplan->plan_mar,
            'plan_apr' => $workplan->plan_apr,
            'plan_may' => $workplan->plan_may,
            'plan_jun' => $workplan->plan_jun,
            'plan_jul' => $workplan->plan_jul,
            'plan_aug' => $workplan->plan_aug,
            'plan_sep' => $workplan->plan_sep,
            'plan_oct' => $workplan->plan_oct,
            'plan_nov' => $workplan->plan_nov,
            'plan_dec' => $workplan->plan_dec,
            'budget' => $workplan->budget,
            'real_jan' => $workplan->real_jan,
            'real_feb' => $workplan->real_feb,
            'real_mar' => $workplan->real_mar,
            'real_apr' => $workplan->real_apr,
            'real_may' => $workplan->real_may,
            'real_jun' => $workplan->real_jun,
            'real_jul' => $workplan->real_jul,
            'real_aug' => $workplan->real_aug,
            'real_sep' => $workplan->real_sep,
            'real_oct' => $workplan->real_oct,
            'real_nov' => $workplan->real_nov,
            'real_dec' => $workplan->real_dec,
            'status' => $workplan->status,
            'description' => $workplan->description
        ];
    }
}
