<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkplanBudgetItem;
use App\Models\Division;
use App\Models\Department;
use App\Models\KPIWorkPlan;

class AnggaranController extends Controller
{

    public function index(Request $request)
    {
        $title = 'Anggaran';
        $year = $request->get('year', date('Y'));

        // Get all divisions with their budget data
        $budgetData = WorkplanBudgetItem::with([
            'workplan',
            'category',
            'budgetCodeRelation'
        ])
        ->whereHas('workplan', function($query) use ($year) {
            $query->where('year', $year);
        })
        ->get()
        ->groupBy(function($item) {
            $divisionName = 'Unknown Division';
            
            if ($item->workplan) {
                // Check kpi_type from workplan
                if ($item->workplan->kpi_type === 'department') {
                    // Load department relation
                    $kpiDepartment = \App\Models\KPIDepartment::find($item->workplan->kpi_id);
                    if ($kpiDepartment && $kpiDepartment->department) {
                        $divisionName = $kpiDepartment->department->division->name ?? 'Unknown Division';
                        
                    }
                } elseif ($item->workplan->kpi_type === 'section') {
                    // Load section relation
                    $kpiSection = \App\Models\KPISection::find($item->workplan->kpi_id);
                    if ($kpiSection && $kpiSection->section) {
                        $divisionName = $kpiSection->section->department->division->name ?? 'Unknown Division';
                    }
                }
            }
            
            return $divisionName;
        });


        return view('pages.Anggaran', compact('title', 'budgetData', 'year'));
    }
    public function resume()
    {
        $title = 'Resume';
        return view('pages.Anggaran_resume', compact('title'));
    }

    public function create()
    {
        // Menampilkan form tambah produk
        $title = 'Input Anggaran';
        return view('pages.Anggaran_Create', compact('title'));
    }

    public function store(Request $request)
    {
        // Menyimpan produk baru
    }

    public function show($id)
    {
        // Menampilkan detail produk
    }

    public function edit($id)
    {
        // Menampilkan form edit produk
        $title = 'Edit Anggaran';
        return view('pages.Anggaran_Edit', compact('title'));
    }

    public function update(Request $request, $id)
    {
        // Update data produk
    }

    public function destroy($id)
    {
        // Hapus produk
    }
}
