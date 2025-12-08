<?php

namespace App\Http\Controllers;

use App\Models\BudgetCode;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BudgetCodeController extends Controller
{
    public function index()
    {
        $codes = BudgetCode::active()->get();
        return response()->json($codes);
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $query = BudgetCode::select(['id', 'code', 'name', 'category', 'description', 'is_active']);

            return DataTables::of($query)
                ->addColumn('status', function ($row) {
                    return $row->is_active 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-warning budgetCode-edit-btn" data-id="' . $row->id . '" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger budgetCode-delete-btn" data-id="' . $row->id . '" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:budget_codes,code',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $budgetCode = BudgetCode::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Budget Code created successfully',
            'data' => $budgetCode
        ]);
    }

    public function edit($id)
    {
        $budgetCode = BudgetCode::findOrFail($id);
        return response()->json($budgetCode);
    }

    public function update(Request $request, $id)
    {
        $budgetCode = BudgetCode::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:budget_codes,code,' . $id,
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $budgetCode->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Budget Code updated successfully',
            'data' => $budgetCode
        ]);
    }

    public function destroy($id)
    {
        $budgetCode = BudgetCode::findOrFail($id);
        
        // Check if used in budget items
        if ($budgetCode->budgetItems()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete budget code that is being used'
            ], 400);
        }

        $budgetCode->delete();

        return response()->json([
            'success' => true,
            'message' => 'Budget Code deleted successfully'
        ]);
    }
}
