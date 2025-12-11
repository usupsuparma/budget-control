<?php

namespace App\Http\Controllers;

use App\Models\BudgetCode;
use App\Models\BudgetCodes;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BudgetCodeController extends Controller
{
    public function index()
    {
        return view('pages.settings.budgetCode');
    }

    public function data()
    {
        $query = BudgetCodes::query();

        return DataTables::of($query)
            ->addColumn('status_label', function ($row) {
                return $row->status == 1
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-light-primary icon-btn-sm edit-btn" data-id="' . $row->id . '">
                        <i class="bi bi-pencil-square"></i>
                    </button>

                    <button class="btn btn-light-danger icon-btn-sm delete-btn" data-id="' . $row->id . '">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                ';
            })
            ->rawColumns(['status_label', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'stock_code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'active_flag' => 'nullable|integer',
            'user_no' => 'nullable|integer',
            'memo' => 'nullable|string',
            'delivdate' => 'nullable|date',
            'inchargeCode' => 'nullable|string|max:100',
            'remarks_id' => 'nullable|integer',
            'remarks' => 'nullable|string',
            'goods_code' => 'nullable|string|max:255',
            'status' => 'nullable|integer'
        ]);

        $save = BudgetCodes::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Budget Code berhasil disimpan',
            'data' => $save
        ]);
    }

    public function edit($id)
    {
        return BudgetCodes::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $code = BudgetCodes::findOrFail($id);

        $validated = $request->validate([
            'stock_code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'active_flag' => 'nullable|integer',
            'user_no' => 'nullable|integer',
            'memo' => 'nullable|string',
            'delivdate' => 'nullable|date',
            'inchargeCode' => 'nullable|string|max:100',
            'remarks_id' => 'nullable|integer',
            'remarks' => 'nullable|string',
            'goods_code' => 'nullable|string|max:255',
            'status' => 'nullable|integer'
        ]);

        $code->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Budget Code berhasil diperbarui',
        ]);
    }

    public function destroy($id)
    {
        BudgetCodes::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Budget Code berhasil dihapus'
        ]);
    }
}
