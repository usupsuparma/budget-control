<?php

namespace App\Http\Controllers;

use App\Models\CompanyPolicy;
use App\Models\CompanyPolicyDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class CompanyPolicyController extends Controller
{
    public function index()
    {
        $title = 'Company Policy';
        // Ambil semua dokumen beserta jumlah detail (strategic goals)
        $policies = CompanyPolicy::with('details')      // ambil strategic goals
            ->withCount('details')               // hitung jumlah goals
            ->orderByDesc('tahun')
            ->orderBy('nama_dokumen')
            ->get();

        // return view('pages.CompanyPolicy', compact('title', 'policies'));
        return view('pages.company-policy.index', compact('title', 'policies'));
    }

    public function create()
    {
        // Menampilkan form tambah company policy
        $title = 'Add Company Policy';

        return view('pages.CompanyPolicy_Create', compact('title'));
    }

    public function store(Request $request)
    {
        // =============== VALIDATION ===============
        $request->validate([
            'tahun'             => 'required|integer',

            // Single fields
            'header'            => 'required|string',
            'contents_en'       => 'required|string',
            'contents_id'       => 'required|string',
            'prologue_en'       => 'required|string',
            'prologue_id'       => 'required|string',
            'closing_en'        => 'required|string',
            'closing_id'        => 'required|string',
            'signature'         => 'required|string',

            // ARRAY FIELDS
            'company_policy_core_en'       => 'required|array|min:1',
            'company_policy_core_en.*'     => 'nullable|string',

            'company_policy_desc_en'       => 'required|array|min:1',
            'company_policy_desc_en.*'     => 'nullable|string',

            'company_policy_core_id'       => 'required|array|min:1',
            'company_policy_core_id.*'     => 'nullable|string',

            'company_policy_desc_id'       => 'required|array|min:1',
            'company_policy_desc_id.*'     => 'nullable|string',
        ], [
            // CUSTOM ERROR MESSAGE
            'tahun.required' => 'Tahun wajib dipilih.',

            'header.required' => 'Header harus diisi.',
            'contents_en.required' => 'Contents (English) wajib diisi.',
            'contents_id.required' => 'Contents (Indonesia) wajib diisi.',
            'prologue_en.required' => 'Prologue (English) wajib diisi.',
            'prologue_id.required' => 'Prologue (Indonesia) wajib diisi.',
            'closing_en.required' => 'Closing (English) wajib diisi.',
            'closing_id.required' => 'Closing (Indonesia) wajib diisi.',
            'signature.required' => 'Signature wajib diisi.',

            'company_policy_core_en.required' => 'Minimal harus ada 1 Company Policy (English).',
            'company_policy_core_id.required' => 'Minimal harus ada 1 Company Policy (Indonesia).'
        ]);


        // =============== SAVE MASTER TABLE ===============
        $policy = CompanyPolicy::create([
            'nama_dokumen' => '0',
            'file_path' => '0',
            'tahun'        => $request->tahun,
            'header'       => $request->header,
            'contents_en'  => $request->contents_en,
            'contents_id'  => $request->contents_id,
            'prologue_en'  => $request->prologue_en,
            'prologue_id'  => $request->prologue_id,
            'closing_en'   => $request->closing_en,
            'closing_id'   => $request->closing_id,
            'signature'    => $request->signature,
        ]);


        // =============== SAVE DETAIL ARRAY ===============
        foreach ($request->company_policy_core_en as $index => $coreEn) {
            CompanyPolicyDetail::create([
                'company_policy_id' => $policy->id,

                'strategic_goal'      => $coreEn,
                'description'         => $request->company_policy_desc_en[$index],
                'strategic_goal_id'   => $request->company_policy_core_id[$index],
                'description_id'      => $request->company_policy_desc_id[$index],

                'target' => '0',
            ]);
        }

        return back()->with('success', 'Company Policy saved successfully');
    }

    public function json($id)
    {
        $policy = CompanyPolicy::with('details')->findOrFail($id);
        return response()->json($policy);
    }

    public function show($id)
    {
        // Menampilkan detail company policy
    }

    public function edit($id)
    {
        // Menampilkan form edit company policy
        $title = 'Edit Company Policy';

        return view('pages.CompanyPolicy_Edit', compact('title'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tahun'             => 'required|integer',

            'header'            => 'required|string',
            'contents_en'       => 'required|string',
            'contents_id'       => 'required|string',
            'prologue_en'       => 'required|string',
            'prologue_id'       => 'required|string',
            'closing_en'        => 'required|string',
            'closing_id'        => 'required|string',
            'signature'         => 'required|string',

            'company_policy_core_en'   => 'required|array|min:1',
            'company_policy_desc_en'   => 'required|array|min:1',
            'company_policy_core_id'   => 'required|array|min:1',
            'company_policy_desc_id'   => 'required|array|min:1',
        ]);

        $policy = CompanyPolicy::findOrFail($id);

        // update master
        $policy->update([
            'tahun'        => $request->tahun,
            'header'       => $request->header,
            'contents_en'  => $request->contents_en,
            'contents_id'  => $request->contents_id,
            'prologue_en'  => $request->prologue_en,
            'prologue_id'  => $request->prologue_id,
            'closing_en'   => $request->closing_en,
            'closing_id'   => $request->closing_id,
            'signature'    => $request->signature,
        ]);

        // replace detail
        $policy->details()->delete();

        foreach ($request->company_policy_core_en as $index => $coreEn) {
            CompanyPolicyDetail::create([
                'company_policy_id' => $policy->id,

                'strategic_goal'    => $coreEn,
                'description'       => $request->company_policy_desc_en[$index] ?? '',
                'strategic_goal_id' => $request->company_policy_core_id[$index] ?? '',
                'description_id'    => $request->company_policy_desc_id[$index] ?? '',

                'target' => '0',
            ]);
        }

        return back()->with('success', 'Company Policy updated successfully');
    }

    public function destroy(CompanyPolicy $dokumen)
    {
        // Hapus company policy
        try {
            // hapus file jika ada
            if ($dokumen->file_path && Storage::disk('public')->exists($dokumen->file_path)) {
                Storage::disk('public')->delete($dokumen->file_path);
            }

            // hapus dokumen (detail_dokumen ikut terhapus kalau FK cascadeOnDelete)
            $dokumen->delete();

            return redirect()
                ->route('company-policy.index')
                ->with('success', 'Dokumen dan seluruh strategic goals berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus dokumen: '.$e->getMessage());
        }
    }

    public function downloadPdf($id)
    {
        // Ambil data policy + detail
        $policy = CompanyPolicy::with('details')->findOrFail($id);

        // Load view PDF
        $pdf = Pdf::loadView('pages.company-policy.pdf', [
            'policy' => $policy,
        ])->setPaper('A4', 'portrait'); // bisa 'landscape' kalau mau

        // Nama file
        $fileName = 'Company-Policy-' . $policy->tahun . '.pdf';

        // return $pdf->download($fileName);
        // atau kalau mau preview di browser:
        return $pdf->stream($fileName);
        // return view('pages.company-policy.pdf', compact('policy'));
    }
}
