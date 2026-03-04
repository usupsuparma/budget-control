<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StockCodeService\StockCodeService;

class SettingCodeController extends Controller
{
    protected $stockCodeService;

    public function __construct(StockCodeService $stockCodeService)
    {
        $this->stockCodeService = $stockCodeService;
    }

    public function index()
    {
        $title = "Code Data";

        return view('pages.settings.SettingCode', compact('title'));
    }

    public function getStockCodeData()
    {
        return $this->stockCodeService->getDataTable();
    }

    public function storeStockCode(Request $request)
    {
        $request->validate([
            'stock_code' => 'required|unique:stock_code,stock_code',
            'name' => 'required',
        ]);

        $this->stockCodeService->store($request->all());

        return response()->json(['success' => true, 'message' => 'Stock Code created successfully']);
    }

    public function editStockCode($id)
    {
        $stockCode = $this->stockCodeService->findById($id);
        return response()->json($stockCode);
    }

    public function updateStockCode(Request $request, $id)
    {
        $request->validate([
            'stock_code' => 'required|unique:stock_code,stock_code,' . $id,
            'name' => 'required',
        ]);

        $this->stockCodeService->update($id, $request->all());

        return response()->json(['success' => true, 'message' => 'Stock Code updated successfully']);
    }

    public function destroyStockCode($id)
    {
        $this->stockCodeService->delete($id);

        return response()->json(['success' => true, 'message' => 'Stock Code deleted successfully']);
    }
}
