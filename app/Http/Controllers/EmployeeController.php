<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Yajra\DataTables\DataTables;

class EmployeeController extends Controller
{


    public function getData()
    {
        $query = Employee::select(['id', 'email', 'first_name', 'last_name', 'role_id', 'status']);

        return DataTables::of($query)
            ->addColumn('full_name', function ($row) {
                return $row->first_name . ' ' . $row->last_name;
            })
            ->addColumn('emails', function ($row) {
                return '<i class="bi bi-envelope me-2 text-muted"></i>' . e($row->email);
            })

            ->addColumn('roles', function ($row) {
                if ($row->role_id == '1') {
                    return '<span class="badge border border-secondary text-secondary">User</span>';
                }
                return '<span class="badge border border-primary text-primary">Admin</span>';
            })

            ->addColumn('status_badge', function ($row) {
                if ($row->status == 'Active') {
                    return '<span class="badge bg-success">Active</span>';
                }
                return '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {

                $editUrl = route('employee.edit', $row->id);

                return '
      
       <button type="button"
                class="btn btn-light-secondary icon-btn-sm open-detail"
                data-id="' . $row->id . '"
                data-bs-toggle="modal"
                data-bs-target="#employeeDetailModal">
                <i class="ri-user-line"></i>
            </button>
            <a href="' . $editUrl . '" class="btn btn-light-primary icon-btn-sm">
                <i class="bi bi-pencil-square"></i>
            </a>

            <button type="button" 
                    class="btn btn-light-danger icon-btn-sm delete-btn" 
                    data-id="' . $row->id . '">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
    ';
            })

            ->rawColumns(['status_badge', 'action', 'emails', 'roles'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'employee_id' => 'required|string|max:50|unique:employee,employee_id',
            'job_position_name' => 'required|string',
            'email' => 'required|email|unique:employee,email',
            'password' => 'required|min:6',
            'role_id' => 'required|integer',
        ]);

        Employee::create([
            'first_name'        => $request->first_name,
            'last_name'         => $request->last_name,
            'employee_id'       => $request->employee_id,
            'job_position_name' => $request->job_position_name,
            'email'             => $request->email,
            'password'          => bcrypt($request->password),
            'role_id'           => $request->role_id,
            'status'            => 'Active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully!'
        ]);
    }

    public function show($id)
    {
        $employee = Employee::with([
            'organization',
            'jobPosition',
            'jobLevel',
            'role'
        ])->findOrFail($id);

        return response()->json($employee);
    }
}
