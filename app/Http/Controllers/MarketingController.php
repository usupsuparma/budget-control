<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\SalesPlanning;
use Illuminate\Http\Request;
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
        $query = SalesPlanning::query();

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
}
