<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyPolicy;
use App\Models\CompanyPolicyDetail;
use Illuminate\Support\Facades\Storage;

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

        return view('pages.CompanyPolicy', compact('title', 'policies'));
    }

    public function create()
    {
        // Menampilkan form tambah company policy
        $title = 'Add Company Policy';
        return view('pages.CompanyPolicy_Create', compact('title'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'tahun'        => 'required|integer',
                'file_dokumen' => 'required|file|mimes:pdf',
                'goal'         => 'required|array|min:1',
                'goal.*'       => 'required|string',
                'deskripsi'    => 'required|array|min:1',
                'deskripsi.*'  => 'required|string',
                'target'       => 'required|array|min:1',
                'target.*'     => 'required|string',
            ]);

            // Upload file
            $path = $request->file('file_dokumen')->store('dokumen', 'public');

            // Simpan ke tabel dokumen
            $dokumen = CompanyPolicy::create([
                'tahun'        => $request->tahun,
                'nama_dokumen' => $request->file('file_dokumen')->getClientOriginalName(),
                'file_path'    => $path,
            ]);

            // Simpan ke tabel detail_dokumen
            foreach ($request->goal as $index => $goal) {
                CompanyPolicyDetail::create([
                    'company_policy_id' => $dokumen->id,
                    'strategic_goal'    => $goal,
                    'description'       => $request->deskripsi[$index] ?? null,
                    'target'            => $request->target[$index] ?? null,
                ]);
            }

            return redirect()->route('company-policy.index')->with('success', 'Dokumen & detail berhasil disimpan.');
        } catch (\Exception $e) {

            return back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
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
        // Update data company policy
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
            return back()->with('error', 'Gagal menghapus dokumen: ' . $e->getMessage());
        }
    }
}
