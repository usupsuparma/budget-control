<?php

namespace App\Http\Controllers;

use App\Models\Segmen;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SegmenController extends Controller
{
    public function data()
    {
        $query = Segmen::select(['id', 'segmen', 'code', 'notes', 'status']);

        return DataTables::of($query)
            ->addColumn('status_badge', function ($row) {
                return $row->status == 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-light-primary icon-btn-sm segmen-edit-btn" data-id="' . $row->id . '">
                        <i class="bi bi-pencil-square"></i>
                    </button>

                    <button class="btn btn-light-danger icon-btn-sm segmen-delete-btn" data-id="' . $row->id . '">
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
            'segmen' => 'required|string|max:255',
            'code' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ]);

        Segmen::create($request->all());

        return response()->json(['message' => 'Segmen saved successfully']);
    }

    public function edit($id)
    {
        return Segmen::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $segmen = Segmen::findOrFail($id);

        $request->validate([
            'segmen' => 'required|string|max:255',
            'code' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $segmen->update($request->all());

        return response()->json(['message' => 'Segmen updated successfully']);
    }

    public function destroy($id)
    {
        Segmen::findOrFail($id)->delete();

        return response()->json(['message' => 'Segmen deleted successfully']);
    }
}
