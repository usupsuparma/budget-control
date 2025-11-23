<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class DepartmentController extends Controller
{
    public function getData()
    {
        $query = Department::with('division')->select(['id', 'name', 'division_id', 'status']);

        return DataTables::of($query)
            ->addColumn('status_badge', function ($row) {
                return $row->status == 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('division', function ($row) {
                return $row->division->name ?? '';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-light-primary icon-btn-sm department-edit-btn" data-id="' . $row->id . '">
                        <i class="bi bi-pencil-square"></i>
                    </button>

                    <button class="btn btn-light-danger icon-btn-sm department-delete-btn" data-id="' . $row->id . '">
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
            'department_name' => 'required|string|max:255',
        ]);

        Department::create([
            'name' => $validated['department_name'],
            'division_id' => $request['division_id'],
            'status' => 'Active',
        ]);

        return redirect()->back()->with('success', 'Department berhasil ditambahkan.');
    }

    public function edit($id)
    {

        $data = Department::with('division')->findOrFail($id);

        return response()->json([
            'id' => $data->id,
            'name' => $data->name,
            'status' => $data->status,
            'division_id' => $data->division_id,
            'division_name' => $data->division ? $data->division->name : null
        ]);
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());
        $validated = $request->validate([
            'department_name'   => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ]);

        $department = Department::findOrFail($id);
        $department->name = $validated['department_name'];
        $department->status = $validated['status'];
        $department->division_id = $request['division_id'];
        $department->save();

        return redirect()->back()->with('success', 'Department berhasil diperbarui.');
    }


    public function destroy($id)
    {
        Department::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
