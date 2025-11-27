<?php

namespace App\Http\Controllers;

use App\Models\KpiDepartment;
use App\Models\KpiSection;
use App\Models\Section;
use Illuminate\Http\Request;

class KPISectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kpiSections = KpiSection::orderBy('id')
            ->get();
        
        $section = Section::orderBy('id')
            ->get();
            
        $kpiDepartments = KPIDepartment::orderBy('id')
            ->get();

        return view('pages.kpi.section', [
            'kpiDepartment' => $kpiDepartments,
            'section'       => $section,
            'kpiSections'   => $kpiSections,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, KpiDepartment $kpiDepartment, Section $section)
    {
        $year = $kpiDepartment->year ?? date('Y');

        $validated = $request->validate([
            'department_goals'  => ['nullable', 'string'],
            'section_id'     => ['required', 'string'],
            'section_goals'     => ['required', 'string'],
            'activities'        => ['nullable', 'string'],
            'target_section'    => ['nullable', 'string'],
            'duration_days'     => ['nullable', 'integer'],
            'schedule_start'    => ['nullable', 'date'],
            'schedule_end'      => ['nullable', 'date'],
            'revenue_cost'      => ['nullable', 'string'],
            'unit_id'           => ['nullable', 'string'],
            'description'       => ['nullable', 'string'],
        ]);

        $toBool = function ($val) {
            if ($val === null) return false;
            $v = strtolower((string) $val);
            return in_array($v, ['1','true','yes','y','ya'], true);
        };

        $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];

        $data = [
            'kpi_department_id' => $validated['department_goals'] ?? null,
            'section_id'        => $validated['section_id'] ?? null,
            'year'              => $year,
            'section_goals'     => $validated['section_goals'],
            'activities'        => $validated['activities'] ?? null,
            'target_section'    => $validated['target_section'] ?? null,
            'duration_days'     => $validated['duration_days'] ?? null,
            'schedule_start'    => $validated['schedule_start'] ?? null,
            'schedule_end'      => $validated['schedule_end'] ?? null,
            'revenue_cost'      => $validated['revenue_cost'] ?? null,
            'unit_id'           => $validated['unit_id'] ?? null,
            'description'       => $validated['description'] ?? null,
        ];

        foreach ($months as $m) {
            $data[$m] = $toBool($request->input($m));
        }

        $kpiSection = KpiSection::create($data);

        return response()->json([
            'status'  => 'success',
            'id'      => $kpiSection->id,
            'message' => 'KPI Section created.',
        ], 201);
    }

    public function inlineUpdate(Request $request, KpiDepartment $kpiDepartment, Section $section, KpiSection $kpiSection)
    {
        if ($kpiSection->kpi_department_id !== $kpiDepartment->id ||
            $kpiSection->section_id        !== $section->id) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Data tidak sesuai dengan parent.',
            ], 403);
        }

        $field = $request->input('field');
        $value = $request->input('value');

        $allowed = [
            'department_goals',
            'section_goals',
            'activities',
            'target_section',
            'duration_days',
            'schedule_start',
            'schedule_end',
            'jan','feb','mar','apr','may','jun',
            'jul','aug','sep','oct','nov','dec',
            'revenue_cost',
            'unit_id',
            'description',
        ];

        if (! in_array($field, $allowed, true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Field tidak boleh diubah inline.',
            ], 422);
        }

        if ($field === 'duration_days') {
            $kpiSection->duration_days = $value !== null ? (int) $value : null;
        } elseif (in_array($field, ['schedule_start','schedule_end'], true)) {
            $request->validate(['value' => ['nullable','date']]);
            $kpiSection->{$field} = $value ?: null;
        } elseif (in_array($field, ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'], true)) {
            $toBool = function ($v) {
                if ($v === null) return false;
                $v = strtolower((string) $v);
                return in_array($v, ['1','true','yes','y','ya'], true);
            };
            $kpiSection->{$field} = $toBool($value);
        } else {
            $kpiSection->{$field} = $value;
        }

        $kpiSection->save();

        $display = $value;
        if (in_array($field, ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'], true)) {
            $display = $kpiSection->{$field} ? 'Yes' : 'No';
        }

        return response()->json([
            'status'        => 'success',
            'message'       => 'Data berhasil diperbarui.',
            'field'         => $field,
            'value'         => $kpiSection->{$field},
            'display_value' => $display,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KpiDepartment $kpiDepartment, Section $section, KpiSection $kpiSection)
    {
        if ($kpiSection->kpi_department_id !== $kpiDepartment->id ||
            $kpiSection->section_id        !== $section->id) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Data tidak sesuai dengan parent.',
            ], 403);
        }

        $kpiSection->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'KPI Section berhasil dihapus.',
        ]);
    }
}
