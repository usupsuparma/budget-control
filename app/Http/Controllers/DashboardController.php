<?php

namespace App\Http\Controllers;

use App\Models\CompanyPolicy;
use App\Models\CompanyPolicyDetail;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Dashboard';
        return view('pages.dashboard', compact('title'));
    }
    public function executive(Request $request)
    {
        $title = 'Dashboard Executive';
        $policies = CompanyPolicy::with('details')      // ambil strategic goals
            ->withCount('details')               // hitung jumlah goals
            ->orderByDesc('tahun')
            ->orderBy('nama_dokumen')
            ->get();

        return view('pages.dash-executive', compact('title','policies'));
    }

    public function executivePoliciesByYear(Request $request)
    {
        $year = (int) $request->query('year');

        $policy = CompanyPolicy::with('details')
            ->where('tahun', $year)
            ->orderBy('nama_dokumen')   // ambil dokumen pertama (atau yang kamu mau)
            ->first();

        $html = view('pages.dash-executive-ajax', compact('policy'))->render();

        return response()->json([
            'status' => 'success',
            'year'   => $year,
            'html'   => $html,
        ]);
    }

}
