<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\JobPosition;
use App\Models\Roles;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class EmployeeController extends Controller
{


    public function getData()
    {
        $query = Employee::with(['role', 'jobPosition'])
            ->select(['id', 'first_name', 'last_name', 'email', 'role_id', 'job_position_id', 'status']);

        return DataTables::of($query)
            ->addColumn('full_name', fn($row) => $row->first_name . ' ' . $row->last_name)
            ->addColumn(
                'email',
                fn($row) =>
                '<i class="bi bi-envelope me-2 text-muted"></i>' . e($row->email)
            )
            ->addColumn(
                'roles',
                fn($row) =>
                '<span class="badge border border-primary text-primary">' . $row->role->name . '</span>'
            )

            ->addColumn(
                'job_position',
                fn($row) =>
                $row->jobPosition->job_position_name ?? '-'
            )

            ->addColumn(
                'status_badge',
                fn($row) =>
                $row->status === 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>'
            )

            ->addColumn('action', function ($row) {
                return '
        <button class="btn btn-light-secondary icon-btn-sm open-detail" 
                data-id="' . $row->id . '">
            <i class="ri-user-line"></i>
        </button>

        <button class="btn btn-light-primary icon-btn-sm employee-edit-btn" 
                data-id="' . $row->id . '">
            <i class="bi bi-pencil-square"></i>
        </button>

        <button class="btn btn-light-warning icon-btn-sm employee-resetpass-btn" 
                data-id="' . $row->id . '">
            <i class="bi bi-shield-lock"></i>
        </button>

        <button class="btn btn-light-danger icon-btn-sm employee-delete-btn" 
                data-id="' . $row->id . '">
            <i class="ri-delete-bin-line"></i>
        </button>
    ';
            })

            ->rawColumns(['roles', 'email', 'status_badge', 'action'])
            ->make(true);
    }


    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            // Ambil data relasi
            $jobPosition = JobPosition::with('jobLevel', 'organization')
                ->findOrFail($request->job_position_id);

            $role = Role::findOrFail($request->role_id);

            // 1️⃣ SIMPAN EMPLOYEE
            $employee = Employee::create([
                'employee_id'      => $request->employee_id,
                'first_name'       => $request->first_name,
                'last_name'        => $request->last_name,
                'email'            => $request->email,
                'password'         => bcrypt($request->password),
                'job_position_id'  => $jobPosition->id,
                'role_id'          => $role->id,
                'status'           => 'Active',
            ]);

            // 2️⃣ SIMPAN EMPLOYMENT
            Employment::create([
                'employee_id'        => $employee->id,

                'organization_id'    => $jobPosition->organization->id ?? null,
                'organization_name'  => $jobPosition->organization->organization_name ?? null,

                'job_level_id'       => $jobPosition->jobLevel->id ?? null,
                'job_level_name'     => $jobPosition->jobLevel->job_level_name ?? null,

                'job_position_id'    => $jobPosition->id,
                'job_position_name'  => $jobPosition->job_position_name,

                'uppline_id'         => $request->uppline_id,
                'uppline_id_name'    => $request->uppline_name,

                'employment_status'  => 'Aktif',
                'role_id'            => $role->id,
                'role_name'          => $role->name,

                'status'             => 'Active',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Employee & Employment created successfully'
        ]);
    }


    public function edit($id)
    {
        $emp = Employee::findOrFail($id);

        return response()->json($emp);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'employee_id' => 'required|string|max:50|unique:employee,employee_id,' . $id,
            'email' => 'required|email|unique:employee,email,' . $id,
            'job_position_id' => 'required|exists:job_position,id',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:Active,Inactive',
        ]);

        $emp = Employee::findOrFail($id);

        $emp->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'employee_id' => $request->employee_id,
            'email' => $request->email,
            'job_position_id' => $request->job_position_id,
            'status' => $request->status,
            'role_id' => $request->role_id, // jika disimpan di table employees
        ]);

        // Spatie Assign Role
        $role = Roles::find($request->role_id);
        $emp->syncRoles([$role->name]);

        return response()->json([
            'success' => true
        ]);
    }


    public function destroy($id)
    {
        Employee::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }


    public function show($id)
    {
        $employee = Employee::with([
            'jobPosition',
            'role',
        ])->findOrFail($id);

        return response()->json($employee);
    }

    public function resetPassword(Request $request, $id)
    {


        $emp = Employee::findOrFail($id);
        $emp->password = bcrypt($request->password);
        $emp->save();

        return response()->json(['success' => true]);
    }
}
