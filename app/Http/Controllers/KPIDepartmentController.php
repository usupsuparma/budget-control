<?php

namespace App\Http\Controllers;

use App\Models\KPIDivision;
use App\Models\KPIDepartment;
use App\Models\Department;
use Illuminate\Http\Request;

class KPIDepartmentController extends Controller
{
    /**
     * Tampilkan halaman KPI Department untuk 1 KPI Division & 1 Department.
     */
    public function index()
    {        
        $kpiDivisions = KpiDivision::orderBy('id')
            ->get();
        
        $department = Department::orderBy('id')
            ->get();
            
        $kpiDepartments = KPIDepartment::orderBy('id')
            ->get();

        return view('pages.kpi.department', [
            'department'     => $department,
            'kpiDepartments' => $kpiDepartments,
            'kpiDivisions'   => $kpiDivisions,
        ]);
    }

    /**
     * Simpan 1 baris baru (Add Row -> Save).
     */
    public function store(Request $request, KPIDivision $kpiDivision, Department $department)
    {
        // Year otomatis ikut dari KPIDivision
        $year = $kpiDivision->year ?? $request->input('year', date('Y'));

        $validated = $request->validate([
            'depatment_id'      => ['required', 'integer'],
            'department_goals'      => ['required', 'string'],
            'division_goals'        => ['nullable', 'string'],
            'department_activities' => ['nullable', 'string'],
            'target_department'     => ['nullable', 'string'],
            'duration_days'         => ['nullable', 'integer'],
            'schedule_start'        => ['nullable', 'date'],
            'schedule_end'          => ['nullable', 'date'],
            'revenue_cost'          => ['nullable', 'string'],
            'pic'                   => ['nullable', 'string'],
            'description'           => ['nullable', 'string'],
        ]);

        $toBool = function ($val) {
            if ($val === null) return false;
            $v = strtolower((string) $val);
            return in_array($v, ['1','true','yes','y','ya'], true);
        };

        $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];

        $data = [
            'kpi_division_id'      => $validated['division_goals'] ?? null,
            'department_id'        => $validated['depatment_id'] ?? null,
            'year'                 => $year,
            'department_goals'     => $validated['department_goals'],
            'department_activities'=> $validated['department_activities'] ?? null,
            'target_department'    => $validated['target_department'] ?? null,
            'duration_days'        => $validated['duration_days'] ?? null,
            'schedule_start'       => $validated['schedule_start'] ?? null,
            'schedule_end'         => $validated['schedule_end'] ?? null,
            'revenue_cost'         => $validated['revenue_cost'] ?? null,
            'pic'                  => $validated['pic'] ?? null,
            'description'          => $validated['description'] ?? null,
        ];

        foreach ($months as $m) {
            $data[$m] = $toBool($request->input($m));
        }

        $kpiDept = KPIDepartment::create($data);

        return response()->json([
            'status'  => 'success',
            'id'      => $kpiDept->id,
            'message' => 'KPI Department created.',
        ], 201);
    }

    /**
     * Inline update satu kolom (double-click cell).
     */
    public function inlineUpdate(Request $request, KPIDivision $kpiDivision, Department $department, KPIDepartment $kpiDepartment)
    {
        if ($kpiDepartment->kpi_division_id !== $kpiDivision->id ||
            $kpiDepartment->department_id   !== $department->id) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Data tidak sesuai dengan parent.',
            ], 403);
        }

        $field = $request->input('field');
        $value = $request->input('value');

        $allowed = [
            'division_goals',
            'department_goals',
            'department_activities',
            'target_department',
            'duration_days',
            'schedule_start',
            'schedule_end',
            'jan','feb','mar','apr','may','jun',
            'jul','aug','sep','oct','nov','dec',
            'revenue_cost',
            'pic',
            'description',
        ];

        if (! in_array($field, $allowed, true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Field tidak boleh diubah inline.',
            ], 422);
        }

        if ($field === 'duration_days') {
            $kpiDepartment->duration_days = $value !== null ? (int) $value : null;
        } elseif (in_array($field, ['schedule_start','schedule_end'], true)) {
            $request->validate(['value' => ['nullable','date']]);
            $kpiDepartment->{$field} = $value ?: null;
        } elseif (in_array($field, ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'], true)) {
            $toBool = function ($v) {
                if ($v === null) return false;
                $v = strtolower((string) $v);
                return in_array($v, ['1','true','yes','y','ya'], true);
            };
            $kpiDepartment->{$field} = $toBool($value);
        } else {
            $kpiDepartment->{$field} = $value;
        }

        $kpiDepartment->save();

        $display = $value;
        if (in_array($field, ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'], true)) {
            $display = $kpiDepartment->{$field} ? 'Yes' : 'No';
        }

        return response()->json([
            'status'        => 'success',
            'message'       => 'Data berhasil diperbarui.',
            'field'         => $field,
            'value'         => $kpiDepartment->{$field},
            'display_value' => $display,
        ]);
    }

    /**
     * Hapus baris KPI Department.
     */
    public function destroy(KPIDivision $kpiDivision, Department $department, KPIDepartment $kpiDepartment)
    {
        if ($kpiDepartment->kpi_division_id !== $kpiDivision->id ||
            $kpiDepartment->department_id   !== $department->id) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Data tidak sesuai dengan parent.',
            ], 403);
        }

        $kpiDepartment->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'KPI Department berhasil dihapus.',
        ]);
    }
}
