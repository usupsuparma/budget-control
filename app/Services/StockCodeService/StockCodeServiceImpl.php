<?php

namespace App\Services\StockCodeService;

use App\Models\StockCode;
use App\Services\LogService\LogService;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class StockCodeServiceImpl implements StockCodeService
{
    protected $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    public function getDataTable()
    {
        $data = StockCode::query();
        
        return DataTables::of($data)
            ->addColumn('action', function($row){
                $btn = '<button class="btn btn-sm btn-info edit-stock-btn" data-id="'.$row->id.'"><i class="bi bi-pencil"></i></button>';
                $btn .= ' <button class="btn btn-sm btn-danger delete-stock-btn" data-id="'.$row->id.'"><i class="bi bi-trash"></i></button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $stockCode = StockCode::create($data);
            
            $this->logService->create(
                'Created new stock code: ' . $stockCode->stock_code,
                [
                    'class' => __CLASS__,
                    'function' => __FUNCTION__,
                    'id' => $stockCode->id,
                    'user_id' => auth()->id(),
                ],
                'info'
            );

            return $stockCode;
        });
    }

    public function findById($id)
    {
        return StockCode::findOrFail($id);
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $stockCode = StockCode::findOrFail($id);
            $stockCode->update($data);

            $this->logService->create(
                'Updated stock code: ' . $stockCode->stock_code,
                [
                    'class' => __CLASS__,
                    'function' => __FUNCTION__,
                    'id' => $id,
                    'user_id' => auth()->id(),
                ],
                'info'
            );

            return $stockCode;
        });
    }

    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            $stockCode = StockCode::findOrFail($id);
            $stockCode->delete();

            $this->logService->create(
                'Deleted stock code: ' . $stockCode->stock_code,
                [
                    'class' => __CLASS__,
                    'function' => __FUNCTION__,
                    'id' => $id,
                    'user_id' => auth()->id(),
                ],
                'info'
            );

            return true;
        });
    }
}
