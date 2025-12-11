<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CustomerController extends Controller
{

    public function data()
    {
        $query = Customer::select(['id', 'callSign', 'address', 'notes', 'status']);

        return DataTables::of($query)
            ->addColumn('status_badge', function ($row) {
                return $row->status == 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-light-primary icon-btn-sm customer-edit-btn" data-id="' . $row->id . '">
                        <i class="bi bi-pencil-square"></i>
                    </button>

                    <button class="btn btn-light-danger icon-btn-sm customer-delete-btn" data-id="' . $row->id . '">
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
            'customer' => 'required|string|max:255',
            'callSign' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ]);

        Customer::create($request->all());

        return response()->json(['message' => 'Customer saved successfully']);
    }

    public function edit($id)
    {
        return Customer::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'customer' => 'required|string|max:255',
            'callSign' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $customer->update($request->all());

        return response()->json(['message' => 'Customer updated successfully']);
    }

    public function destroy($id)
    {
        Customer::findOrFail($id)->delete();

        return response()->json(['message' => 'Customer deleted successfully']);
    }
}
