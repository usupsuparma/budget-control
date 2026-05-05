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
        $request->validate([
            'tahun' => 'required|integer',

            'company_name' => 'required|string',
            'place_date' => 'required|string',
            'document_title' => 'required|string',
            'subtitle' => 'required|string',

            'refer_to_en' => 'nullable|array',
            'refer_to_en.*' => 'nullable|string',
            'refer_to_id' => 'nullable|array',
            'refer_to_id.*' => 'nullable|string',

            'considering_en' => 'nullable|array',
            'considering_en.*' => 'nullable|string',
            'considering_id' => 'nullable|array',
            'considering_id.*' => 'nullable|string',

            'decision_en' => 'nullable|string',
            'decision_id' => 'nullable|string',

            'background_en' => 'required|string',
            'background_id' => 'required|string',

            'prologue_en' => 'required|string',
            'prologue_id' => 'required|string',

            'closing_en' => 'required|string',
            'closing_id' => 'required|string',

            'signature_position' => 'required|array|min:1',
            'signature_position.*' => 'required|string',
            'signature_name' => 'required|array|min:1',
            'signature_name.*' => 'required|string',
            'signature_image' => 'nullable|array',
            'signature_image.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'company_policy_core_en' => 'required|array|min:1',
            'company_policy_core_en.*' => 'nullable|string',

            'company_policy_desc_en' => 'required|array|min:1',
            'company_policy_desc_en.*' => 'nullable|string',

            'company_policy_core_id' => 'required|array|min:1',
            'company_policy_core_id.*' => 'nullable|string',

            'company_policy_desc_id' => 'required|array|min:1',
            'company_policy_desc_id.*' => 'nullable|string',
        ], [
            'tahun.required' => 'Tahun wajib dipilih.',
            'company_name.required' => 'Nama perusahaan wajib diisi.',
            'document_title.required' => 'Judul dokumen wajib diisi.',
            'subtitle.required' => 'Subtitle wajib diisi.',
            'place_date.required' => 'Tempat dan tanggal wajib diisi.',
            'background_en.required' => 'Background English wajib diisi.',
            'background_id.required' => 'Latar Belakang Indonesia wajib diisi.',
            'prologue_en.required' => 'Company Policy English wajib diisi.',
            'prologue_id.required' => 'Company Policy Indonesia wajib diisi.',
            'closing_en.required' => 'Closing English wajib diisi.',
            'closing_id.required' => 'Closing Indonesia wajib diisi.',
            'signature_position.required' => 'Minimal harus ada 1 posisi penandatangan.',
            'signature_position.*.required' => 'Posisi penandatangan wajib diisi.',
            'signature_name.required' => 'Minimal harus ada 1 nama penandatangan.',
            'signature_name.*.required' => 'Nama penandatangan wajib diisi.',
            'signature_image.*.image' => 'File tanda tangan harus berupa gambar.',
            'signature_image.*.mimes' => 'File tanda tangan harus berformat jpg, jpeg, atau png.',
            'signature_image.*.max' => 'Ukuran file tanda tangan maksimal 2MB.',
            'company_policy_core_en.required' => 'Minimal harus ada 1 Company Policy English.',
            'company_policy_core_id.required' => 'Minimal harus ada 1 Company Policy Indonesia.',
        ]);

        $header = '
            <h3>' . e($request->document_title) . '</h3>
            <h3>' . e($request->company_name) . '</h3>
            <p>=================================</p>
            <h3><em>[' . e($request->subtitle) . ']</em></h3>
            <p>' . e($request->place_date) . '</p>
        ';

        $buildOrderedList = function ($items) {
            $html = '<ol>';
            foreach ($items ?? [] as $item) {
                if (trim((string) $item) !== '') {
                    $html .= '<li>' . e($item) . '</li>';
                }
            }
            $html .= '</ol>';
            return $html;
        };

        $contentsEn = '
            <h3>REFER TO:</h3>' . $buildOrderedList($request->refer_to_en) . '
            <h3>CONSIDERING:</h3>' . $buildOrderedList($request->considering_en) . '
            <h3>DECISION:</h3>
            <p>' . e($request->decision_en) . '</p>
            <h3>Background:</h3>' . $request->background_en;

        $contentsId = '
            <h3>MENGACU PADA:</h3>' . $buildOrderedList($request->refer_to_id) . '
            <h3>MEMPERTIMBANGKAN:</h3>' . $buildOrderedList($request->considering_id) . '
            <h3>MEMUTUSKAN:</h3>
            <p>' . e($request->decision_id) . '</p>
            <h3>Latar Belakang:</h3>' . $request->background_id;

        $positions = $request->signature_position ?? [];
        $names = $request->signature_name ?? [];
        $images = $request->file('signature_image', []);
        $signatureCount = count($positions);

        $signature = '<h3 style="text-align:center;">THE BOARD OF DIRECTORS / DEWAN DIREKSI</h3>';
        $signature .= '<table width="100%" cellspacing="0" cellpadding="8" style="width:100%; text-align:center; margin-top:20px; border-collapse:collapse;">';

        $signature .= '<tr>';
        for ($i = 0; $i < $signatureCount; $i++) {
            $signature .= '<td style="vertical-align:top;"><b>' . e($positions[$i]) . '</b></td>';
        }
        $signature .= '</tr>';

        $signature .= '<tr>';
        for ($i = 0; $i < $signatureCount; $i++) {
            $signature .= '<td style="height:90px; vertical-align:middle; text-align:center;">';
            if (isset($images[$i]) && $images[$i]) {
                $path = $images[$i]->store('company-policy/signatures', 'public');
                $signature .= '<img src="' . asset('storage/' . $path) . '" style="max-height:70px; max-width:160px;">';
            }
            $signature .= '</td>';
        }
        $signature .= '</tr>';

        $signature .= '<tr>';
        for ($i = 0; $i < $signatureCount; $i++) {
            $signature .= '<td style="vertical-align:top;"><u><b>' . e($names[$i]) . '</b></u></td>';
        }
        $signature .= '</tr>';

        $signature .= '</table>';

        $policy = CompanyPolicy::create([
            'nama_dokumen' => 'Company Policy FY' . $request->tahun,
            'file_path' => '0',
            'tahun' => $request->tahun,
            'header' => $header,
            'contents_en' => $contentsEn,
            'contents_id' => $contentsId,
            'prologue_en' => $request->prologue_en,
            'prologue_id' => $request->prologue_id,
            'closing_en' => $request->closing_en,
            'closing_id' => $request->closing_id,
            'signature' => $signature,
        ]);

        foreach ($request->company_policy_core_en as $index => $coreEn) {
            CompanyPolicyDetail::create([
                'company_policy_id' => $policy->id,
                'strategic_goal' => $coreEn ?? '',
                'description' => $request->company_policy_desc_en[$index] ?? '',
                'strategic_goal_id' => $request->company_policy_core_id[$index] ?? '',
                'description_id' => $request->company_policy_desc_id[$index] ?? '',
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
            'tahun' => 'required|integer',

            'company_name' => 'required|string',
            'place_date' => 'required|string',
            'document_title' => 'required|string',
            'subtitle' => 'required|string',

            'refer_to_en' => 'nullable|array',
            'refer_to_en.*' => 'nullable|string',
            'refer_to_id' => 'nullable|array',
            'refer_to_id.*' => 'nullable|string',

            'considering_en' => 'nullable|array',
            'considering_en.*' => 'nullable|string',
            'considering_id' => 'nullable|array',
            'considering_id.*' => 'nullable|string',

            'decision_en' => 'nullable|string',
            'decision_id' => 'nullable|string',

            'background_en' => 'required|string',
            'background_id' => 'required|string',

            'prologue_en' => 'required|string',
            'prologue_id' => 'required|string',

            'closing_en' => 'required|string',
            'closing_id' => 'required|string',

            'signature_position' => 'required|array|min:1',
            'signature_position.*' => 'required|string',
            'signature_name' => 'required|array|min:1',
            'signature_name.*' => 'required|string',

            'company_policy_core_en' => 'required|array|min:1',
            'company_policy_core_en.*' => 'nullable|string',

            'company_policy_desc_en' => 'required|array|min:1',
            'company_policy_desc_en.*' => 'nullable|string',

            'company_policy_core_id' => 'required|array|min:1',
            'company_policy_core_id.*' => 'nullable|string',

            'company_policy_desc_id' => 'required|array|min:1',
            'company_policy_desc_id.*' => 'nullable|string',
        ]);

        $policy = CompanyPolicy::findOrFail($id);

        $header = '
            <h3>' . e($request->document_title) . '</h3>
            <h3>' . e($request->company_name) . '</h3>
            <p>=================================</p>
            <h3><em>[' . e($request->subtitle) . ']</em></h3>
            <p>' . e($request->place_date) . '</p>
        ';

        $referToEn = '<ol>';
        foreach ($request->refer_to_en ?? [] as $item) {
            if (!empty($item)) {
                $referToEn .= '<li>' . e($item) . '</li>';
            }
        }
        $referToEn .= '</ol>';

        $referToId = '<ol>';
        foreach ($request->refer_to_id ?? [] as $item) {
            if (!empty($item)) {
                $referToId .= '<li>' . e($item) . '</li>';
            }
        }
        $referToId .= '</ol>';

        $consideringEn = '<ol>';
        foreach ($request->considering_en ?? [] as $item) {
            if (!empty($item)) {
                $consideringEn .= '<li>' . e($item) . '</li>';
            }
        }
        $consideringEn .= '</ol>';

        $consideringId = '<ol>';
        foreach ($request->considering_id ?? [] as $item) {
            if (!empty($item)) {
                $consideringId .= '<li>' . e($item) . '</li>';
            }
        }
        $consideringId .= '</ol>';

        $contentsEn = '
            <h3>REFER TO:</h3>' . $referToEn . '
            <h3>CONSIDERING:</h3>' . $consideringEn . '
            <h3>DECISION:</h3>
            <p>' . e($request->decision_en) . '</p>
            <h3>Background:</h3>' . $request->background_en;

        $contentsId = '
            <h3>MENGACU PADA:</h3>' . $referToId . '
            <h3>MEMPERTIMBANGKAN:</h3>' . $consideringId . '
            <h3>MEMUTUSKAN:</h3>
            <p>' . e($request->decision_id) . '</p>
            <h3>Latar Belakang:</h3>' . $request->background_id;

        $positions = $request->signature_position ?? [];
        $names = $request->signature_name ?? [];
        $count = max(count($positions), count($names));

        $signature = '<h3 style="text-align:center;">THE BOARD OF DIRECTORS / DEWAN DIREKSI</h3>';
        $signature .= '<table width="100%" style="text-align:center; margin-top:20px;">';

        $signature .= '<tr>';
        for ($i = 0; $i < $count; $i++) {
            $signature .= '<td style="padding:10px;"><b>' . e($positions[$i] ?? '-') . '</b></td>';
        }
        $signature .= '</tr>';

        $signature .= '<tr>';
        for ($i = 0; $i < $count; $i++) {
            $signature .= '<td style="height:80px;"></td>';
        }
        $signature .= '</tr>';

        $signature .= '<tr>';
        for ($i = 0; $i < $count; $i++) {
            $signature .= '<td><u><b>' . e($names[$i] ?? '-') . '</b></u></td>';
        }
        $signature .= '</tr>';

        $signature .= '</table>';

        $policy->update([
            'nama_dokumen' => 'Company Policy FY' . $request->tahun,
            'tahun' => $request->tahun,
            'header' => $header,
            'contents_en' => $contentsEn,
            'contents_id' => $contentsId,
            'prologue_en' => $request->prologue_en,
            'prologue_id' => $request->prologue_id,
            'closing_en' => $request->closing_en,
            'closing_id' => $request->closing_id,
            'signature' => $signature,
        ]);

        $policy->details()->delete();

        foreach ($request->company_policy_core_en as $index => $coreEn) {
            CompanyPolicyDetail::create([
                'company_policy_id' => $policy->id,
                'strategic_goal' => $coreEn ?? '',
                'description' => $request->company_policy_desc_en[$index] ?? '',
                'strategic_goal_id' => $request->company_policy_core_id[$index] ?? '',
                'description_id' => $request->company_policy_desc_id[$index] ?? '',
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
