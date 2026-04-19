<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Services\MasterDataService\MasterDataService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class DepartmentController extends Controller
{
    public function __construct(
        protected MasterDataService $masterDataService
    ) {}

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
            'division_id'     => 'required|exists:division,id',
        ]);

        Department::create([
            'name' => $validated['department_name'],
            'division_id' => $validated['division_id'],
            'status' => 'Active',
        ]);

        $this->masterDataService->forgetCache();

        return response()->json(['success' => true, 'message' => 'Department created successfully.']);
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
        $validated = $request->validate([
            'department_name'   => 'required|string|max:255',
            'division_id'       => 'required|exists:division,id',
            'status'            => 'required|in:Active,Inactive',
        ]);

        $department = Department::findOrFail($id);
        $department->name = $validated['department_name'];
        $department->division_id = $validated['division_id'];
        $department->status = $validated['status'];
        $department->save();

        $this->masterDataService->forgetCache();

        return response()->json(['success' => true, 'message' => 'Department updated successfully.']);
    }

    public function destroy($id)
    {
        Department::findOrFail($id)->delete();
        $this->masterDataService->forgetCache();

        return response()->json(['success' => true]);
    }
}
