<?php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BudgetCategoryController extends Controller
{
    public function index()
    {
        $categories = BudgetCategory::with('parent')
            ->active()
            ->ordered()
            ->get();
        
        return response()->json($categories);
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $query = BudgetCategory::with('parent')
                ->select(['id', 'code', 'name', 'parent_id', 'level', 'sort_order', 'is_active', 'description']);

            return DataTables::of($query)
                ->addColumn('parent_name', function ($row) {
                    return $row->parent ? $row->parent->name : '-';
                })
                ->addColumn('status', function ($row) {
                    return $row->is_active 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-light-primary icon-btn-sm budgetCategory-edit-btn" data-id="' . $row->id . '" data-bs-toggle="tooltip" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-light-danger icon-btn-sm budgetCategory-delete-btn" data-id="' . $row->id . '" data-bs-toggle="tooltip" title="Delete">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    ';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:budget_categories,code',
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:budget_categories,id',
            'level' => 'required|integer|min:1|max:2',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $category = BudgetCategory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Budget Category created successfully',
            'data' => $category
        ]);
    }

    public function edit($id)
    {
        $category = BudgetCategory::with('parent')->findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = BudgetCategory::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:budget_categories,code,' . $id,
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:budget_categories,id',
            'level' => 'required|integer|min:1|max:2',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Budget Category updated successfully',
            'data' => $category
        ]);
    }

    public function destroy($id)
    {
        $category = BudgetCategory::findOrFail($id);
        
        // Check if has children
        if ($category->hasChildren()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with child categories'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Budget Category deleted successfully'
        ]);
    }

    public function getParentCategories()
    {
        $parents = BudgetCategory::parentOnly()
            ->active()
            ->ordered()
            ->get();
        
        return response()->json($parents);
    }
}
