<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AreaController extends Controller
{
    public function data()
    {
        $query = Area::select(['id', 'continent_province', 'country_city', 'code', 'notes', 'status']);

        return DataTables::of($query)
            ->addColumn('status_badge', function ($row) {
                return $row->status == 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-light-primary icon-btn-sm area-edit-btn" data-id="' . $row->id . '">
                        <i class="bi bi-pencil-square"></i>
                    </button>

                    <button class="btn btn-light-danger icon-btn-sm area-delete-btn" data-id="' . $row->id . '">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                ';
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {

        $request->validate([
            'country_city' => 'required|string|max:255',
            'code' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ]);

        Area::create($request->all());

        return response()->json(['message' => 'Area saved successfully']);
    }

    public function edit($id)
    {
        return Area::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $area = Area::findOrFail($id);

        $request->validate([
            'country_city' => 'required|string|max:255',
            'code' => 'nullable|string',
            'status' => 'required|integer|in:0,1'

        ]);

        $area->update($request->all());

        return response()->json(['message' => 'Area updated successfully']);
    }

    public function destroy($id)
    {
        Area::findOrFail($id)->delete();

        return response()->json(['message' => 'Area deleted successfully']);
    }
}
