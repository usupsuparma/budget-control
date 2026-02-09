<?php

namespace App\Http\Controllers;

use App\Models\KPISectionCompanyPolicy;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class KPISectionCompanyPolicyController extends Controller
{
    public function dataTable()
    {
        $rows = KPISectionCompanyPolicy::orderBy('tahun', 'desc')->get()->map(function ($r) {
            return [
                'id'    => $r->id,
                'tahun' => $r->tahun,
                'file'  => '<a href="' . route('kpisectioncompanypolicy.pdf', $r->id) . '" target="_blank"
                                class="btn btn-workplan btn-sm">
                                Document PDF
                            </a>', // (opsional) ganti jadi link PDF kalau kamu punya route PDF
            ];
        });

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun'       => ['required', 'integer'],
            'header'      => ['nullable', 'string'],
            'contents_en' => ['nullable', 'string'],
            'contents_id' => ['nullable', 'string'],
            'prologue_en' => ['nullable', 'string'],
            'prologue_id' => ['nullable', 'string'],
            'closing_en'  => ['nullable', 'string'],
            'closing_id'  => ['nullable', 'string'],
            'signature'   => ['nullable', 'string'],
        ]);

        // 1 tahun 1 policy (optional, tapi biasanya iya)
        $cp = KPISectionCompanyPolicy::updateOrCreate(
            ['tahun' => $validated['tahun']],
            $validated
        );

        return response()->json([
            'status'  => 'success',
            'id'      => $cp->id,
            'message' => 'Company Policy by KPI Section saved.',
        ], 201);
    }

    public function show($id)
    {
        $cp = KPISectionCompanyPolicy::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $cp,
        ]);
    }

    public function update(Request $request, $id)
    {
        $cp = KPISectionCompanyPolicy::findOrFail($id);

        $validated = $request->validate([
            'tahun'       => ['required', 'integer'],
            'header'      => ['nullable', 'string'],
            'contents_en' => ['nullable', 'string'],
            'contents_id' => ['nullable', 'string'],
            'prologue_en' => ['nullable', 'string'],
            'prologue_id' => ['nullable', 'string'],
            'closing_en'  => ['nullable', 'string'],
            'closing_id'  => ['nullable', 'string'],
            'signature'   => ['nullable', 'string'],
        ]);

        $cp->update($validated);

        return response()->json([
            'status'  => 'success',
            'id'      => $cp->id,
            'message' => 'Company Policy by KPI Section updated.',
        ]);
    }

    public function destroy($id)
    {
        $cp = KPISectionCompanyPolicy::find($id);

        if (!$cp) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $cp->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Company Policy by KPI Section berhasil dihapus.',
        ]);
    }

    public function downloadPdf($id)
    {
        // Ambil data policy + detail
        $policy = KPISectionCompanyPolicy::findOrFail($id);

        // Load view PDF
        $pdf = Pdf::loadView('pages.kpi.section-company-policy-pdf', [
            'policy' => $policy,
        ])->setPaper('A4', 'portrait'); // bisa 'landscape' kalau mau

        // Nama file
        $fileName = 'KPI-Section-Company-Policy-' . $policy->tahun . '.pdf';

        // return $pdf->download($fileName);
        // atau kalau mau preview di browser:
        return $pdf->stream($fileName);
        // return view('pages.kpi.section-company-policy-pdf', compact('policy'));
    }
}
