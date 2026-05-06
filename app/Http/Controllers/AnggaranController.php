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
        $budgetItems = WorkplanBudgetItem::with([
            'workplan',
            'category',
            'budgetCodeRelation'
        ])
        ->whereHas('workplan', function($query) use ($year) {
            $query->where('year', $year);
        })
        ->get();

        // First group by division, then by budget_category_id
        $budgetData = $budgetItems->groupBy(function($item) {
            $divisionName = 'Unknown Division';
            
            if ($item->workplan) {
                // Check kpi_type from workplan
                if ($item->workplan->kpi_type === 'department') {
                    // Load department relation
                    $KPIDepartment = \App\Models\KPIDepartment::find($item->workplan->kpi_id);
                    if ($KPIDepartment && $KPIDepartment->department) {
                        $divisionName = $KPIDepartment->department->division->name ?? 'Unknown Division';
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
        })->map(function($divisionItems) {
            // Group by budget_category_id and merge activities and totals
            return $divisionItems->groupBy('budget_category_id')->map(function($categoryItems) {
                $first = $categoryItems->first();
                
                // Sum the total
                $totalSum = $categoryItems->sum('total');
                
                // Merge monthly activities (if any month is active in any item, it should be active)
                $mergedActivities = [];
                foreach(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'] as $month) {
                    $mergedActivities["activity_$month"] = $categoryItems->contains(function($item) use ($month) {
                        return $item->{"activity_$month"} == 1;
                    }) ? 1 : 0;
                }
                
                // Create a merged object
                $merged = clone $first;
                $merged->total = $totalSum;
                
                // Update monthly activities
                foreach($mergedActivities as $key => $value) {
                    $merged->$key = $value;
                }
                
                return $merged;
            })->values();
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
