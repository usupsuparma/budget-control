<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stock_code';

    protected $fillable = [
        'stock_code',
        'name',
        'unit',
        'budget_code',
        'active',
        'warehouse',
        'category',
        'product_line',
    ];
}
