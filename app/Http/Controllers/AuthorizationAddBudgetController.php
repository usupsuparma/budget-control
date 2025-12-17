<?php

namespace App\Http\Controllers;

use App\Models\AddBudgetAuthorizer;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class AuthorizationAddBudgetController extends Controller
{
    public function index()
    {
        $employees = Employee::orderBy('first_name')->get();
        return view('pages.authorizationAddBudget.index', compact('employees'));
    }

    public function data()
    {
        try {
            $query = AddBudgetAuthorizer::with('employee');

            return DataTables::of($query)
                ->addColumn('authorizer_name', fn($row) => $row->authorizer_name ?? '-')
                ->addColumn(
                    'employee_name',
                    fn($row) =>
                    trim(($row->employee->first_name ?? '') . ' ' . ($row->employee->last_name ?? ''))
                )
                ->addColumn('status_badge', function ($row) {
                    return $row->status == 1
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                    <button class="btn btn-sm btn-warning transactionAuthorizer-edit-btn"
                        data-id="' . $row->id . '">Edit</button>
                    <button class="btn btn-sm btn-danger transactionAuthorizer-delete-btn"
                        data-id="' . $row->id . '">Delete</button>
                ';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'level_number' => 'required|numeric',
            'authorizer'   => 'required',
            'authority'    => 'required',
            'employee'     => 'required',
            'status'       => 'required|numeric',
        ]);

        AddBudgetAuthorizer::create([
            'level_number' => $request->level_number,
            'authorizer_name' => $request->authorizer,
            'authority' => $request->authority,
            'employee_id' => $request->employee,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        return AddBudgetAuthorizer::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $row = AddBudgetAuthorizer::findOrFail($id);

        $row->update([
            'level_number' => $request->level_number,
            'authorizer' => $request->authorizer,
            'authority' => $request->authority,
            'employee_id' => $request->employee,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        AddBudgetAuthorizer::findOrFail($id)->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
