<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\JobPosition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class EmployeeController extends Controller
{


    public function getData()
    {
        // Use 'roles' (Spatie HasRoles trait) instead of 'role' to avoid conflict with scopeRole()
        // Load employment and job position via employment
        $query = Employee::with(['roles', 'employment.jobPosition', 'employment.jobLevel'])
            ->select(['id', 'first_name', 'last_name', 'email', 'employee_code', 'status']);

        return DataTables::of($query)
            ->addColumn('full_name', function ($row) {
                return
                    e($row->first_name . ' ' . $row->last_name) .
                    '<br>' .
                    '<small class="text-muted"><i class="bi bi-envelope me-1"></i>' . e($row->email) . '</small>';
            })

            ->addColumn('employee_code', function ($row) {
                return '<span class="badge bg-info">' . e($row->employee_code ?? '-') . '</span>';
            })

            ->addColumn('job_info', function ($row) {
                $employment = $row->employment;
                $jp = $employment?->jobPosition;
                $jl = $employment?->jobLevel;

                return
                    e($jp?->job_position_name ?? '-') .
                    '<br>' .
                    '<small class="text-muted">' . e($jl?->job_level_name ?? '-') . '</small>';
            })
            ->addColumn(
                'roles',
                fn($row) =>
                '<span class="badge border border-primary text-primary">' . ($row->roles->first()->name ?? '-') . '</span>'
            )

            ->addColumn(
                'job_position',
                fn($row) =>
                $row->employment?->jobPosition?->job_position_name ?? '-'
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

            // Custom filter for full_name column (search in first_name, last_name, and email)
            ->filterColumn('full_name', function ($query, $keyword) {
                $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$keyword}%"])
                    ->orWhere('email', 'LIKE', "%{$keyword}%");
            })

            // Custom filter for job_info column (search via employment)
            ->filterColumn('job_info', function ($query, $keyword) {
                $query->whereHas('employment.jobPosition', function ($q) use ($keyword) {
                    $q->where('job_position_name', 'LIKE', "%{$keyword}%");
                })->orWhereHas('employment.jobLevel', function ($q) use ($keyword) {
                    $q->where('job_level_name', 'LIKE', "%{$keyword}%");
                });
            })

            // Custom filter for roles column (search in Spatie roles relationship)
            ->filterColumn('roles', function ($query, $keyword) {
                $query->whereHas('roles', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%");
                });
            })

            ->rawColumns(['full_name', 'employee_code', 'job_info', 'roles', 'email', 'status_badge', 'action'])
            ->make(true);
    }


    public function store(Request $request)
    {
        // Validasi dengan pesan error yang jelas
        $validated = $request->validate([
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'employee_code'   => 'required|string|max:50|unique:employee,employee_code',
            'email'           => 'required|email|max:150|unique:employee,email',
            'password'        => 'required|string|min:6',
            'job_position_id' => 'required|exists:job_position,id',
            'role_name'       => 'required|string|exists:roles,name',
        ], [
            // Pesan error dalam Bahasa Indonesia
            'first_name.required'      => 'Nama depan wajib diisi',
            'first_name.max'           => 'Nama depan maksimal 100 karakter',
            'last_name.required'       => 'Nama belakang wajib diisi',
            'last_name.max'            => 'Nama belakang maksimal 100 karakter',
            'employee_code.required'   => 'Nomor Induk Pegawai (NIP) wajib diisi',
            'employee_code.unique'     => 'Nomor Induk Pegawai (NIP) sudah terdaftar',
            'employee_code.max'        => 'NIP maksimal 50 karakter',
            'email.required'           => 'Email wajib diisi',
            'email.email'              => 'Format email tidak valid',
            'email.unique'             => 'Email sudah terdaftar, gunakan email lain',
            'email.max'                => 'Email maksimal 150 karakter',
            'password.required'        => 'Password wajib diisi',
            'password.min'             => 'Password minimal 6 karakter',
            'job_position_id.required' => 'Job Position wajib dipilih',
            'job_position_id.exists'   => 'Job Position tidak valid',
            'role_name.required'       => 'Role wajib dipilih',
            'role_name.exists'         => 'Role tidak valid',
        ]);

        try {
            DB::transaction(function () use ($request) {

                // Ambil data relasi
                $jobPosition = JobPosition::findOrFail($request->job_position_id);

                // Get role by name (from Spatie)
                $role = Role::where('name', $request->role_name)->firstOrFail();

                // 1️⃣ SIMPAN EMPLOYEE
                $employee = Employee::create([
                    'employee_code'    => $request->employee_code, // NIP
                    'first_name'       => $request->first_name,
                    'last_name'        => $request->last_name,
                    'email'            => $request->email,
                    'password'         => bcrypt($request->password),
                    'job_position_id'  => $jobPosition->id,
                    'status'           => 'Active',
                ]);

                // Assign role via Spatie
                $employee->assignRole($role->name);

                // 2️⃣ SIMPAN EMPLOYMENT (employee_id = FK ke employee.id)
                Employment::create([
                    'employee_id'        => $employee->id, // FK ke employee.id

                    'organization_id'    => $jobPosition->organization->id ?? null,
                    'organization_name'  => $jobPosition->organization->organization_name ?? null,

                    'job_level_id'       => $jobPosition->job_level_id ?? null,
                    'job_level_name'     => $jobPosition->job_level_name ?? null,

                    'job_position_id'    => $jobPosition->id,
                    'job_position_name'  => $jobPosition->job_position_name,

                    'uppline_id'         => $request->uppline_id ?? null,
                    'uppline_id_name'    => $request->uppline_name ?? null,

                    'employment_status'  => 'Aktif',
                    'status'             => 'Active',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Employee & Employment created successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('EmployeeController@store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data employee. Silakan coba lagi.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function edit($id)
    {
        // Eager load roles for Spatie
        $emp = Employee::with(['employment', 'roles'])->findOrFail($id);

        return response()->json($emp);
    }



    public function update(Request $request, $id)
    {
        // Validasi dengan pesan error yang jelas
        $validated = $request->validate([
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'employee_code'   => 'required|string|max:50|unique:employee,employee_code,' . $id,
            'email'           => 'required|email|max:150|unique:employee,email,' . $id,
            'job_position_id' => 'required|exists:job_position,id',
            'role_name'       => 'required|string|exists:roles,name',
            'status'          => 'required|in:Active,Inactive',
        ], [
            // Pesan error dalam Bahasa Indonesia
            'first_name.required'      => 'Nama depan wajib diisi',
            'first_name.max'           => 'Nama depan maksimal 100 karakter',
            'last_name.required'       => 'Nama belakang wajib diisi',
            'last_name.max'            => 'Nama belakang maksimal 100 karakter',
            'employee_code.required'   => 'Nomor Induk Pegawai (NIP) wajib diisi',
            'employee_code.unique'     => 'Nomor Induk Pegawai (NIP) sudah digunakan oleh employee lain',
            'employee_code.max'        => 'NIP maksimal 50 karakter',
            'email.required'           => 'Email wajib diisi',
            'email.email'              => 'Format email tidak valid',
            'email.unique'             => 'Email sudah digunakan oleh employee lain',
            'email.max'                => 'Email maksimal 150 karakter',
            'job_position_id.required' => 'Job Position wajib dipilih',
            'job_position_id.exists'   => 'Job Position tidak valid',
            'role_name.required'       => 'Role wajib dipilih',
            'role_name.exists'         => 'Role tidak valid',
            'status.required'          => 'Status wajib dipilih',
            'status.in'                => 'Status harus Active atau Inactive',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {

                $emp = Employee::findOrFail($id);
                $jobPosition = JobPosition::findOrFail($request->job_position_id);

                // Get role by name (from Spatie)
                $role = Role::where('name', $request->role_name)->firstOrFail();

                // Update Employee
                $emp->update([
                    'first_name'      => $request->first_name,
                    'last_name'       => $request->last_name,
                    'employee_code'   => $request->employee_code, // NIP
                    'email'           => $request->email,
                    'status'          => $request->status,
                ]);

                // Sync role via Spatie (replaces all existing roles)
                $emp->syncRoles([$role->name]);

                // Ambil employment berdasarkan employee.id (FK)
                $employment = Employment::where('employee_id', $emp->id)->first();

                // Hanya update jika employment memang ada
                if ($employment) {
                    $employment->update([
                        'job_position_id'   => $jobPosition->id,
                        'job_position_name' => $jobPosition->job_position_name,

                        'organization_id'   => $jobPosition->organization_id ?? null,
                        'organization_name' => $jobPosition->organization_name ?? null,

                        'job_level_id'      => $jobPosition->job_level_id ?? null,
                        'job_level_name'    => $jobPosition->job_level_name ?? null,

                        'uppline_id'        => $request->uppline_id ?? null,
                        'uppline_id_name'   => $request->uppline_name ?? null,

                        'employment_status' => $request->status === 'Active' ? 'Aktif' : 'Unaktif',
                        'status'            => $request->status,
                    ]);
                }
            });

            return response()->json(['success' => true, 'message' => 'Employee updated successfully']);
        } catch (\Exception $e) {
            Log::error('EmployeeController@update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data employee. Silakan coba lagi.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        DB::transaction(function () use ($id) {

            $emp = Employee::findOrFail($id);

            // Soft delete employment (FK = employee.id)
            Employment::where('employee_id', $emp->id)->delete();

            // Soft delete employee
            $emp->delete();
        });

        return response()->json(['success' => true]);
    }



    public function show($id)
    {
        try {
            $employee = Employee::with([
                'employment.jobPosition',
                'employment.jobLevel',
                'roles',
            ])->findOrFail($id);

            return response()->json($employee);
        } catch (\Throwable $th) {
            Log::error("EmployeeController@show: ", [
                'error' => $th->getMessage(),
                'id' => $id
            ]);
            return response()->json(['error' => 'Employee not found'], 404);
        }
    }

    public function resetPassword(Request $request, $id)
    {


        $emp = Employee::findOrFail($id);
        $emp->password = bcrypt($request->password);
        $emp->save();

        return response()->json(['success' => true]);
    }
}
