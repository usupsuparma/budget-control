<?php

namespace App\Http\Controllers;

use App\Models\KPIDivision;
use App\Models\KPIDepartment;
use App\Models\Department;
use Illuminate\Http\Request;

class KPIDepartmentController extends Controller
{
    /**
     * Halaman utama KPI Department.
     * Hanya kirim data untuk dropdown (KPI Division & Department).
     * Data tabel akan di-load via AJAX (dataTable()).
     */
    public function index()
    {
        $title        = 'KPI Department';
        $kpiDivisions = KPIDivision::orderBy('year')
            ->orderBy('division_goals')
            ->get();

        $departments  = Department::orderBy('name')->get();

        return view('pages.kpi.department_rev1', [
            'title'        => $title,
            'kpiDivisions' => $kpiDivisions,
            'departments'  => $departments,
        ]);
    }

    /**
     * DataTables AJAX source.
     */
    public function dataTable()
    {
        $items = KPIDepartment::with(['kpiDivision', 'department'])
            ->orderBy('id', 'desc')
            ->get();

        $rows = [];
        $no   = 1;

        foreach ($items as $row) {
            $rows[] = [
                'id'                   => $row->id,
                'no'                   => $no++,
                'year'                 => $row->year,
                'kpi_division'         => optional($row->kpiDivision)->division_goals ?? '-',
                'kpi_division_id'      => $row->kpi_division_id,
                'department'           => optional($row->department)->name ?? '-',
                'department_id'        => $row->department_id,
                'department_goals'     => $row->department_goals,
                'department_activities'=> $row->department_activities,
                'target_department'    => $row->target_department,
                'duration_days'        => $row->duration_days,
                'schedule_start'       => optional($row->schedule_start)->format('Y-m-d'),
                'schedule_end'         => optional($row->schedule_end)->format('Y-m-d'),
                'jan'                  => (bool) $row->jan,
                'feb'                  => (bool) $row->feb,
                'mar'                  => (bool) $row->mar,
                'apr'                  => (bool) $row->apr,
                'may'                  => (bool) $row->may,
                'jun'                  => (bool) $row->jun,
                'jul'                  => (bool) $row->jul,
                'aug'                  => (bool) $row->aug,
                'sep'                  => (bool) $row->sep,
                'oct'                  => (bool) $row->oct,
                'nov'                  => (bool) $row->nov,
                'dec'                  => (bool) $row->dec,
                'revenue_cost'         => $row->revenue_cost,
                'pic'                  => $row->pic,
                'description'          => $row->description,
            ];
        }

        return response()->json([
            'data' => $rows,
        ]);
    }

    /**
     * Helper konversi input bulan (checkbox / select) ke boolean.
     */
    protected function toBool($val): bool
    {
        if ($val === null) {
            return false;
        }

        $v = strtolower((string) $val);
        return in_array($v, ['1', 'true', 'yes', 'y', 'ya'], true);
    }

    /**
     * Store (Create) – dipanggil dari modal Add.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year'                 => ['required', 'integer'],
            'kpi_division_id'      => ['required', 'exists:kpi_division,id'],
            'department_id'        => ['required', 'exists:department,id'],

            'department_goals'     => ['required', 'string'],
            'department_activities'=> ['nullable', 'string'],
            'target_department'    => ['nullable', 'string'],
            'duration_days'        => ['nullable', 'integer'],
            'schedule_start'       => ['nullable', 'date'],
            'schedule_end'         => ['nullable', 'date'],

            'revenue_cost'         => ['nullable', 'string'],
            'pic'                  => ['nullable', 'string'],
            'description'          => ['nullable', 'string'],

            // bulan akan diproses manual dari request
        ]);

        $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];

        $data = [
            'year'                  => $validated['year'],
            'kpi_division_id'       => $validated['kpi_division_id'],
            'department_id'         => $validated['department_id'],
            'department_goals'      => $validated['department_goals'],
            'department_activities' => $validated['department_activities'] ?? null,
            'target_department'     => $validated['target_department'] ?? null,
            'duration_days'         => $validated['duration_days'] ?? null,
            'schedule_start'        => $validated['schedule_start'] ?? null,
            'schedule_end'          => $validated['schedule_end'] ?? null,
            'revenue_cost'          => $validated['revenue_cost'] ?? null,
            'pic'                   => $validated['pic'] ?? null,
            'description'           => $validated['description'] ?? null,
        ];

        foreach ($months as $m) {
            $data[$m] = $this->toBool($request->input($m));
        }

        $kpiDept = KPIDepartment::create($data);

        return response()->json([
            'status'  => 'success',
            'id'      => $kpiDept->id,
            'message' => 'KPI Department row created successfully.',
        ], 201);
    }

    /**
     * Show – untuk isi modal Edit lewat AJAX.
     */
    public function show($id)
    {
        $kpiDept = KPIDepartment::with(['kpiDivision', 'department'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $kpiDept,
        ]);
    }

    /**
     * Update – dipanggil dari modal Edit.
     */
    public function update(Request $request, $id)
    {
        $kpiDept = KPIDepartment::findOrFail($id);

        $validated = $request->validate([
            'year'                 => ['required', 'integer'],
            'kpi_division_id'      => ['required', 'exists:kpi_division,id'],
            'department_id'        => ['required', 'exists:department,id'],

            'department_goals'     => ['required', 'string'],
            'department_activities'=> ['nullable', 'string'],
            'target_department'    => ['nullable', 'string'],
            'duration_days'        => ['nullable', 'integer'],
            'schedule_start'       => ['nullable', 'date'],
            'schedule_end'         => ['nullable', 'date'],

            'revenue_cost'         => ['nullable', 'string'],
            'pic'                  => ['nullable', 'string'],
            'description'          => ['nullable', 'string'],
        ]);

        $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];

        $data = [
            'year'                  => $validated['year'],
            'kpi_division_id'       => $validated['kpi_division_id'],
            'department_id'         => $validated['department_id'],
            'department_goals'      => $validated['department_goals'],
            'department_activities' => $validated['department_activities'] ?? null,
            'target_department'     => $validated['target_department'] ?? null,
            'duration_days'         => $validated['duration_days'] ?? null,
            'schedule_start'        => $validated['schedule_start'] ?? null,
            'schedule_end'          => $validated['schedule_end'] ?? null,
            'revenue_cost'          => $validated['revenue_cost'] ?? null,
            'pic'                   => $validated['pic'] ?? null,
            'description'           => $validated['description'] ?? null,
        ];

        foreach ($months as $m) {
            $data[$m] = $this->toBool($request->input($m));
        }

        $kpiDept->update($data);

        return response()->json([
            'status'  => 'success',
            'id'      => $kpiDept->id,
            'message' => 'KPI Department row updated successfully.',
        ]);
    }

    /**
     * Delete – hapus 1 baris KPI Department.
     */
    public function destroy($id)
    {
        $kpiDept = KPIDepartment::findOrFail($id);
        $kpiDept->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'KPI Department berhasil dihapus.',
        ]);
    }
}
