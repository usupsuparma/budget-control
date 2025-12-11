<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class UnitController extends Controller
{

    public function data()
    {
        $query = Unit::select(['id', 'unit', 'code', 'notes', 'status']);

        return DataTables::of($query)
            ->addColumn('status_badge', function ($row) {
                return $row->status == 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-light-primary icon-btn-sm unit-edit-btn" data-id="' . $row->id . '">
                        <i class="bi bi-pencil-square"></i>
                    </button>

                    <button class="btn btn-light-danger icon-btn-sm unit-delete-btn" data-id="' . $row->id . '">
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
            'unit' => 'required|string|max:255',
            'code' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ]);

        Unit::create($request->all());

        return response()->json(['message' => 'Unit saved successfully']);
    }

    public function edit($id)
    {
        return Unit::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $request->validate([
            'unit' => 'required|string|max:255',
            'code' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $unit->update($request->all());

        return response()->json(['message' => 'Unit updated successfully']);
    }

    public function destroy($id)
    {
        Unit::findOrFail($id)->delete();

        return response()->json(['message' => 'Unit deleted successfully']);
    }
}
