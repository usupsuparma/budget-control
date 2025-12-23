<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionDetail extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'transaction_id',
        'budget_id',
        'budget_name',
        'goods_service_name',
        'balance',
        'estimated_price',
        'estimated_quantity',
        'estimated_total',
        'fix_price',
        'fix_quantity',
        'fix_total',
        'unit_id',
        'unit_name',
        'remark',
        'urgency',
        'status',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
