<?php

namespace App\Http\Controllers;

use App\Models\KPIDepartment;
use App\Models\KPISection;
use App\Models\Section;
use Illuminate\Http\Request;

class KPISectionController extends Controller
{
    public function index()
    {
        $title = 'KPI Section';

        // dropdown modal
        $kpiDepartments = KPIDepartment::orderBy('id', 'desc')->get();
        $sections       = Section::orderBy('name')->get();

        return view('pages.kpi.section_rev1', compact('title', 'kpiDepartments', 'sections'));
    }

    /**
     * DataTables AJAX
     */
    public function dataTable(Request $request)
    {
        $rows = KPISection::with(['kpiDepartment', 'section'])
            ->orderBy('id', 'desc')
            ->get();

        $data = $rows->map(function ($kpi, $i) {
            $monthKeys = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];

            $months = [];
            foreach ($monthKeys as $m) {
                $months[$m] = [
                    'value' => (int) $kpi->{$m},
                    'label' => $kpi->{$m} ? 'Yes' : 'No',
                ];
            }

            return [
                'no' => $i + 1,
                'id' => $kpi->id,

                'year' => $kpi->year,
                'kpi_department_id' => $kpi->kpi_department_id,
                'kpi_department' => optional($kpi->kpiDepartment)->department_goals ?? '-',

                'section_id' => $kpi->section_id,
                'section' => optional($kpi->section)->name ?? '-',

                'section_goals' => $kpi->section_goals,
                'activities' => $kpi->activities,
                'target_section' => $kpi->target_section,
                'duration_days' => $kpi->duration_days,
                'schedule_start' => optional($kpi->schedule_start)->format('Y-m-d'),
                'schedule_end' => optional($kpi->schedule_end)->format('Y-m-d'),

                // months
                ...$months,

                'revenue_cost' => $kpi->revenue_cost,
                'unit_id' => $kpi->unit_id,
                'description' => $kpi->description,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'kpi_department_id' => ['required', 'exists:kpi_department,id'],
            'section_id' => ['required', 'exists:section,id'],

            'section_goals' => ['required', 'string'],
            'activities' => ['nullable', 'string'],
            'target_section' => ['nullable', 'string'],
            'duration_days' => ['nullable', 'integer'],
            'schedule_start' => ['nullable', 'date'],
            'schedule_end' => ['nullable', 'date'],

            'jan' => ['nullable'],
            'feb' => ['nullable'],
            'mar' => ['nullable'],
            'apr' => ['nullable'],
            'may' => ['nullable'],
            'jun' => ['nullable'],
            'jul' => ['nullable'],
            'aug' => ['nullable'],
            'sep' => ['nullable'],
            'oct' => ['nullable'],
            'nov' => ['nullable'],
            'dec' => ['nullable'],

            'revenue_cost' => ['nullable', 'string'],
            'unit_id' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $toBool = function ($val) {
            if (is_null($val)) return false;
            $val = strtolower((string) $val);
            return in_array($val, ['1', 'true', 'yes', 'y', 'ya'], true);
        };

        $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];

        $data = [
            'year' => $validated['year'],
            'kpi_department_id' => $validated['kpi_department_id'],
            'section_id' => $validated['section_id'],

            'section_goals' => $validated['section_goals'],
            'activities' => $validated['activities'] ?? null,
            'target_section' => $validated['target_section'] ?? null,
            'duration_days' => $validated['duration_days'] ?? null,
            'schedule_start' => $validated['schedule_start'] ?? null,
            'schedule_end' => $validated['schedule_end'] ?? null,

            'revenue_cost' => $validated['revenue_cost'] ?? null,
            'unit_id' => $validated['unit_id'] ?? null,
            'description' => $validated['description'] ?? null,
        ];

        foreach ($months as $m) {
            $data[$m] = $toBool($request->input($m));
        }

        $kpi = KPISection::create($data);

        return response()->json([
            'status' => 'success',
            'id' => $kpi->id,
            'message' => 'KPI Section created successfully.',
        ], 201);
    }

    public function show($id)
    {
        $kpi = KPISection::with(['kpiDepartment', 'section'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $kpi->id,
                'year' => $kpi->year,
                'kpi_department_id' => $kpi->kpi_department_id,
                'section_id' => $kpi->section_id,
                'section_goals' => $kpi->section_goals,
                'activities' => $kpi->activities,
                'target_section' => $kpi->target_section,
                'duration_days' => $kpi->duration_days,
                'schedule_start' => optional($kpi->schedule_start)->format('Y-m-d'),
                'schedule_end' => optional($kpi->schedule_end)->format('Y-m-d'),
                'jan' => (int) $kpi->jan,
                'feb' => (int) $kpi->feb,
                'mar' => (int) $kpi->mar,
                'apr' => (int) $kpi->apr,
                'may' => (int) $kpi->may,
                'jun' => (int) $kpi->jun,
                'jul' => (int) $kpi->jul,
                'aug' => (int) $kpi->aug,
                'sep' => (int) $kpi->sep,
                'oct' => (int) $kpi->oct,
                'nov' => (int) $kpi->nov,
                'dec' => (int) $kpi->dec,
                'revenue_cost' => $kpi->revenue_cost,
                'unit_id' => $kpi->unit_id,
                'description' => $kpi->description,
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $kpi = KPISection::findOrFail($id);

        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'kpi_department_id' => ['required', 'exists:kpi_department,id'],
            'section_id' => ['required', 'exists:section,id'],

            'section_goals' => ['required', 'string'],
            'activities' => ['nullable', 'string'],
            'target_section' => ['nullable', 'string'],
            'duration_days' => ['nullable', 'integer'],
            'schedule_start' => ['nullable', 'date'],
            'schedule_end' => ['nullable', 'date'],

            'jan' => ['nullable'],
            'feb' => ['nullable'],
            'mar' => ['nullable'],
            'apr' => ['nullable'],
            'may' => ['nullable'],
            'jun' => ['nullable'],
            'jul' => ['nullable'],
            'aug' => ['nullable'],
            'sep' => ['nullable'],
            'oct' => ['nullable'],
            'nov' => ['nullable'],
            'dec' => ['nullable'],

            'revenue_cost' => ['nullable', 'string'],
            'unit_id' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $toBool = function ($val) {
            if (is_null($val)) return false;
            $val = strtolower((string) $val);
            return in_array($val, ['1', 'true', 'yes', 'y', 'ya'], true);
        };

        $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];

        $kpi->fill([
            'year' => $validated['year'],
            'kpi_department_id' => $validated['kpi_department_id'],
            'section_id' => $validated['section_id'],

            'section_goals' => $validated['section_goals'],
            'activities' => $validated['activities'] ?? null,
            'target_section' => $validated['target_section'] ?? null,
            'duration_days' => $validated['duration_days'] ?? null,
            'schedule_start' => $validated['schedule_start'] ?? null,
            'schedule_end' => $validated['schedule_end'] ?? null,

            'revenue_cost' => $validated['revenue_cost'] ?? null,
            'unit_id' => $validated['unit_id'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        foreach ($months as $m) {
            $kpi->{$m} = $toBool($request->input($m));
        }

        $kpi->save();

        return response()->json([
            'status' => 'success',
            'message' => 'KPI Section updated successfully.',
        ]);
    }

    public function destroy($id)
    {
        $kpi = KPISection::findOrFail($id);
        $kpi->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'KPI Section deleted successfully.',
        ]);
    }
}
