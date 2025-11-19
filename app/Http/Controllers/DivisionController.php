<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class DivisionController extends Controller
{
    public function getData()
    {
        $query = Division::select(['id', 'name', 'status']);

        return DataTables::of($query)
            ->addColumn('status_badge', function ($row) {
                return $row->status == 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-light-primary icon-btn-sm edit-btn" data-id="' . $row->id . '">
                        <i class="bi bi-pencil-square"></i>
                    </button>

                    <button class="btn btn-light-danger icon-btn-sm delete-btn" data-id="' . $row->id . '">
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
            'name' => 'required|string|max:255',
        ]);

        Division::create([
            'name' => $validated['name'],
            'status' => 'Active', // default
        ]);

        return redirect()->back()->with('success', 'Division berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data = Division::findOrFail($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        $division = Division::findOrFail($id);
        $division->name = $validated['name'];
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
