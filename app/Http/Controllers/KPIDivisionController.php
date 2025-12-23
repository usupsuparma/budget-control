<?php

namespace App\Http\Controllers;

use App\Models\CompanyPolicyDetail;
use App\Models\Division;
use App\Models\KPIDivision;
use Illuminate\Http\Request;

class KPIDivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = 'KPI Division';
        $companyPolicies = CompanyPolicyDetail::with('dokumen')
            ->orderBy('id', 'desc')
            ->get();

        $divisions = Division::orderBy('name')->get();

        return view('pages.kpi.division_rev1', compact('title', 'companyPolicies', 'divisions'));
    }

    public function dataTable()
    {
        // Kalau butuh relasi, sekalian load
        $kpis = KPIDivision::with(['companyPolicy', 'division'])
            ->orderBy('id', 'desc')
            ->get();

        $no = 1;
        $rows = [];

        foreach ($kpis as $kpi) {
            $rows[] = [
                'id'              => $kpi->id,
                'no'              => $no++,
                'year'            => $kpi->year,
                'company_policy'  => optional($kpi->companyPolicy)->strategic_goal ?? '-',
                'division'        => optional($kpi->division)->name ?? 'Division #'.$kpi->division_id,
                'division_goals'  => $kpi->division_goals,
                'target_division' => $kpi->target_division,
                'duration_days'   => $kpi->duration_days,
                'schedule_start'  => optional($kpi->schedule_start)->format('Y-m-d'),
                'schedule_end'    => optional($kpi->schedule_end)->format('Y-m-d'),

                // bulan (kalau masih mau pakai dari DB)
                'jan' => (bool) $kpi->jan,
                'feb' => (bool) $kpi->feb,
                'mar' => (bool) $kpi->mar,
                'apr' => (bool) $kpi->apr,
                'may' => (bool) $kpi->may,
                'jun' => (bool) $kpi->jun,
                'jul' => (bool) $kpi->jul,
                'aug' => (bool) $kpi->aug,
                'sep' => (bool) $kpi->sep,
                'oct' => (bool) $kpi->oct,
                'nov' => (bool) $kpi->nov,
                'dec' => (bool) $kpi->dec,

                'revenue_cost' => $kpi->revenue_cost,
                'pic'          => $kpi->pic,
                'description'  => $kpi->description,
            ];
        }

        return response()->json([
            'data' => $rows, // format standar DataTables
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
    public function store(Request $request)
    {
        // validasi 1 baris
        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'company_policy_detail_id' => ['required', 'exists:company_policy_detail,id'],
            'division_id' => ['required', 'exists:division,id'],

            'division_goals' => ['required', 'string'],
            'target_division' => ['nullable', 'string'],
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
            'pic' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $toBool = function ($val) {
            if (is_null($val)) {
                return false;
            }
            $val = strtolower((string) $val);

            return in_array($val, ['1', 'true', 'yes', 'y', 'ya'], true);
        };

        $kpi = KPIDivision::create([
            'company_policy_detail_id' => $validated['company_policy_detail_id'],
            'division_id' => $validated['division_id'],
            'year' => $validated['year'],

            'division_goals' => $validated['division_goals'],
            'target_division' => $validated['target_division'] ?? null,
            'duration_days' => $validated['duration_days'] ?? null,
            'schedule_start' => $validated['schedule_start'] ?? null,
            'schedule_end' => $validated['schedule_end'] ?? null,

            'jan' => $toBool($request->jan),
            'feb' => $toBool($request->feb),
            'mar' => $toBool($request->mar),
            'apr' => $toBool($request->apr),
            'may' => $toBool($request->may),
            'jun' => $toBool($request->jun),
            'jul' => $toBool($request->jul),
            'aug' => $toBool($request->aug),
            'sep' => $toBool($request->sep),
            'oct' => $toBool($request->oct),
            'nov' => $toBool($request->nov),
            'dec' => $toBool($request->dec),

            'revenue_cost' => $validated['revenue_cost'] ?? null,
            'pic' => $validated['pic'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'id' => $kpi->id,
            'message' => 'KPI Division row created successfully.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $kpi = KPIDivision::with(['companyPolicy', 'division'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $kpi,
        ], 200);
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
    public function update(Request $request, $id)
    {
        $kpi = KPIDivision::findOrFail($id);

        // validasi sama seperti store
        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'company_policy_detail_id' => ['required', 'exists:company_policy_detail,id'],
            'division_id' => ['required', 'exists:division,id'],

            'division_goals' => ['required', 'string'],
            'target_division' => ['nullable', 'string'],
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
            'pic' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $toBool = function ($val) {
            if (is_null($val)) {
                return false;
            }
            $val = strtolower((string) $val);

            return in_array($val, ['1', 'true', 'yes', 'y', 'ya'], true);
        };

        $kpi->update([
            'company_policy_detail_id' => $validated['company_policy_detail_id'],
            'division_id' => $validated['division_id'],
            'year' => $validated['year'],

            'division_goals' => $validated['division_goals'],
            'target_division' => $validated['target_division'] ?? null,
            'duration_days' => $validated['duration_days'] ?? null,
            'schedule_start' => $validated['schedule_start'] ?? null,
            'schedule_end' => $validated['schedule_end'] ?? null,

            'jan' => $toBool($request->jan),
            'feb' => $toBool($request->feb),
            'mar' => $toBool($request->mar),
            'apr' => $toBool($request->apr),
            'may' => $toBool($request->may),
            'jun' => $toBool($request->jun),
            'jul' => $toBool($request->jul),
            'aug' => $toBool($request->aug),
            'sep' => $toBool($request->sep),
            'oct' => $toBool($request->oct),
            'nov' => $toBool($request->nov),
            'dec' => $toBool($request->dec),

            'revenue_cost' => $validated['revenue_cost'] ?? null,
            'pic' => $validated['pic'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'status'  => 'success',
            'id'      => $kpi->id,
            'message' => 'KPI Division row updated successfully.',
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $kpi = KpiDivision::find($id);

        if (! $kpi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        try {
            $kpi->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'KPI Division berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data: '.$e->getMessage(),
            ], 500);
        }
    }

    public function inlineUpdate(Request $request, $id)
    {
        $kpi = KpiDivision::find($id);

        if (! $kpi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $field = $request->input('field');
        $value = $request->input('value');

        // daftar kolom yang boleh di-edit inline
        $allowed = [
            'year',
            'division_goals',
            'target_division',
            'duration_days',
            'schedule_start',
            'schedule_end',
            'jan', 'feb', 'mar', 'apr', 'may', 'jun',
            'jul', 'aug', 'sep', 'oct', 'nov', 'dec',
            'revenue_cost',
            'pic',
            'description',
        ];

        if (! in_array($field, $allowed, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Field tidak boleh diubah inline.',
            ], 422);
        }

        // sedikit casting tipe
        if ($field === 'year') {
            $request->validate([
                'value' => ['required', 'integer'],
            ]);
            $kpi->year = (int) $value;
        } elseif ($field === 'duration_days') {
            $kpi->duration_days = $value !== null ? (int) $value : null;
        } elseif (in_array($field, ['schedule_start', 'schedule_end'], true)) {
            $request->validate([
                'value' => ['nullable', 'date'],
            ]);
            $kpi->{$field} = $value ?: null;
        } elseif (in_array($field, ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'], true)) {
            // terima 1/0, yes/no, true/false
            $toBool = function ($v) {
                if ($v === null) {
                    return false;
                }
                $v = strtolower((string) $v);

                return in_array($v, ['1', 'true', 'yes', 'y', 'ya'], true);
            };
            $kpi->{$field} = $toBool($value);
        } else {
            // sisanya anggap string biasa
            $kpi->{$field} = $value;
        }

        $kpi->save();

        // tampilan text di tabel (misal for Yes/No)
        $displayValue = $value;

        if (in_array($field, ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'], true)) {
            $displayValue = $kpi->{$field} ? 'Yes' : 'No';
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diperbarui.',
            'field' => $field,
            'value' => $kpi->{$field},
            'display_value' => $displayValue,
        ]);
    }
}
