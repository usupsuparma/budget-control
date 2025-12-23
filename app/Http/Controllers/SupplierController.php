<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SupplierController extends Controller
{


    public function data()
    {
        $query = Supplier::select(['id', 'supplier', 'callSign', 'address', 'notes', 'status']);

        return DataTables::of($query)
            ->addColumn('status_badge', function ($row) {
                return $row->status == 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-light-primary icon-btn-sm supplier-edit-btn" data-id="' . $row->id . '">
                        <i class="bi bi-pencil-square"></i>
                    </button>

                    <button class="btn btn-light-danger icon-btn-sm supplier-delete-btn" data-id="' . $row->id . '">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                ';
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {

        $request->validate([
            'supplier' => 'required|string|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ]);

        Supplier::create($request->all());

        return response()->json(['message' => 'Supplier saved successfully']);
    }

    public function edit($id)
    {
        return Supplier::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'supplier' => 'required|string|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $supplier->update($request->all());

        return response()->json(['message' => 'Supplier updated successfully']);
    }

    public function destroy($id)
    {
        Supplier::findOrFail($id)->delete();

        return response()->json(['message' => 'Supplier deleted successfully']);
    }
}
