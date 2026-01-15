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
        // Use 'roles' (Spatie HasRoles trait) instead of 'role' to avoid conflict with scopeRole()
        $query = Employee::with(['roles', 'jobPosition'])
            ->select(['id', 'first_name', 'last_name', 'email', 'role_id', 'job_position_id', 'status']);

        return DataTables::of($query)
            ->addColumn('full_name', function ($row) {
                return
                    e($row->first_name . ' ' . $row->last_name) .
                    '<br>' .
                    '<small class="text-muted"><i class="bi bi-envelope me-1"></i>' . e($row->email) . '</small>';
            })

            ->addColumn('job_info', function ($row) {
                $jp = $row->jobPosition;

                return
                    e($jp->job_position_name ?? '-') .
                    '<br>' .
                    '<small class="text-muted">' . e($jp->job_level_name ?? '-') . '</small>';
            })
            ->addColumn(
                'roles',
                fn($row) =>
                '<span class="badge border border-primary text-primary">' . ($row->roles->first()->name ?? '-') . '</span>'
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

 

        <button class="btn btn-light-danger icon-btn-sm employee-delete-btn" 
                data-id="' . $row->id . '">
            <i class="ri-delete-bin-line"></i>
        </button>
    ';
            })

            ->rawColumns(['full_name', 'job_info', 'roles', 'email', 'status_badge', 'action'])
            ->make(true);
    }


    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            // Ambil data relasi
            $jobPosition = JobPosition::findOrFail($request->job_position_id);

            $role = Role::findOrFail($request->role_id);

            // 1️⃣ SIMPAN EMPLOYEE

            //dd($request->all());
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
                'employee_id'        => $request->employee_id,

                'organization_id'    => $jobPosition->organization->id ?? null,
                'organization_name'  => $jobPosition->organization->organization_name ?? null,

                'job_level_id'       => $jobPosition->job_level_id ?? null,
                'job_level_name'     => $jobPosition->job_level_name ?? null,

                'job_position_id'    => $jobPosition->id,
                'job_position_name'  => $jobPosition->job_position_name,

                'uppline_id'         => $request->uppline_id ?? null,
                'uppline_id_name'    => $request->uppline_name ?? null,

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

        $emp = Employee::with('employment')->findOrFail($id);

        return response()->json($emp);
    }



    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {

            $emp = Employee::findOrFail($id);
            $jobPosition = JobPosition::findOrFail($request->job_position_id);
            $role = Role::findOrFail($request->role_id);

            // Simpan employee_id lama sebelum update
            $oldEmployeeId = $emp->employee_id;

            // Update Employee
            $emp->update([
                'first_name'      => $request->first_name,
                'last_name'       => $request->last_name,
                'employee_id'     => $request->employee_id,
                'email'           => $request->email,
                'job_position_id' => $jobPosition->id,
                'role_id'         => $role->id,
                'status'          => $request->status,
            ]);

            // Ambil employment berdasarkan employee_id LAMA
            $employment = Employment::where('employee_id', $oldEmployeeId)->first();

            // Jika tidak ada, coba cari dengan employee_id baru (untuk kasus seeder baru)
            if (!$employment) {
                $employment = Employment::where('employee_id', $request->employee_id)->first();
            }

            // Hanya update jika employment memang ada
            if ($employment) {
                $employment->update([
                    'employee_id'       => $request->employee_id, // Update ke employee_id baru
                    'job_position_id'   => $jobPosition->id,
                    'job_position_name' => $jobPosition->job_position_name,

                    'organization_id'   => $jobPosition->organization_id ?? null,
                    'organization_name' => $jobPosition->organization_name ?? null,

                    'job_level_id'      => $jobPosition->job_level_id ?? null,
                    'job_level_name'    => $jobPosition->job_level_name ?? null,

                    'uppline_id'        => $request->uppline_id ?? null,
                    'uppline_id_name'   => $request->uppline_name ?? null,

                    'employment_status' => $request->status === 'Active' ? 'Aktif' : 'Unaktif',
                    'role_id'           => $role->id,
                    'role_name'         => $role->name,
                    'status'            => $request->status,
                ]);
            }
        });

        return response()->json(['success' => true]);
    }


    public function destroy($id)
    {
        DB::transaction(function () use ($id) {

            $emp = Employee::findOrFail($id);

            // Soft delete employment
            Employment::where('employee_id', $emp->employee_id)->delete();

            // Soft delete employee
            $emp->delete();
        });

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
