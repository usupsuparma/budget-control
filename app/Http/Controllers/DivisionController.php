<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class DivisionController extends Controller
{
    public function getData()
    {
        $query = Division::with('director')->select(['id', 'name', 'director_id', 'status']);

        return DataTables::of($query)
            ->addColumn('director', function ($row) {
                return $row->director->name ?? '';
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status == 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-light-primary icon-btn-sm division-edit-btn" data-id="' . $row->id . '">
                        <i class="bi bi-pencil-square"></i>
                    </button>

                    <button class="btn btn-light-danger icon-btn-sm division-delete-btn" data-id="' . $row->id . '">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                ';
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'division_name' => 'required|string|max:255',
        ]);

        Division::create([
            'name' => $validated['division_name'],
            'director_id' => $request['director_id'],
            'status' => 'Active', // default
        ]);

        return redirect()->back()->with('success', 'Division berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data = Division::with('director')->findOrFail($id);

        return response()->json([
            'id' => $data->id,
            'name' => $data->name,
            'status' => $data->status,
            'director_id' => $data->director_id,
            'director_name' => $data->director ? $data->director->name : null
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'division_name' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        $division = Division::findOrFail($id);
        $division->name = $validated['division_name'];
        $division->director_id = $request['director_id'];
        $division->status = $validated['status'];
        $division->save();

        return redirect()->back()->with('success', 'Division berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Division::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
