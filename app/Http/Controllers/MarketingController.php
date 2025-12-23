<?php

namespace App\Http\Controllers;

use App\Exports\MarketingPlanTemplateExport;
use App\Imports\MarketingPlanImport;
use App\Models\Division;
use App\Models\MarketingPlan;
use App\Models\SalesPlanning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class MarketingController extends Controller
{
    public function index(Request $request)
    {
        $divisions = Division::all();

        return view('pages.sales-plan.marketing-plan', compact('divisions'));
    }

    public function store(Request $request)
    {

        SalesPlanning::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data saved successfully'
        ]);
    }

    public function getData(Request $request)
    {
        $query = MarketingPlan::query();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return '
                <button class="btn btn-warning btn-sm edit-btn" data-id="' . $row->id . '">Edit</button>
                <button class="btn btn-danger btn-sm delete-btn" data-id="' . $row->id . '">Delete</button>
            ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function uploadExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        return response()->json([
            'validated' => true,
            'filename' => $request->file('file')->getClientOriginalName(),
        ]);
    }

    public function downloadTemplate()
    {
        return Excel::download(new MarketingPlanTemplateExport, 'marketing_plan_template.xlsx');
    }
}
