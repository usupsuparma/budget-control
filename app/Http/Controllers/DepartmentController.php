<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class DepartmentController extends Controller
{
    public function getData()
    {
        $query = Department::select(['id', 'name', 'status']);

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

        Department::create([
            'name' => $validated['name'],
            'status' => 'Active',
        ]);

        return redirect()->back()->with('success', 'Department berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data = Department::findOrFail($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        $department = Department::findOrFail($id);
        $department->name = $validated['name'];
        $department->status = $validated['status'];
        $department->save();

        return redirect()->back()->with('success', 'Department berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Department::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
