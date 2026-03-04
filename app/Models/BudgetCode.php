<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetCode extends Model
{
    protected $table = 'budget_code';
    protected $guarded = [];

    protected $fillable = [
        'budget_code',
        'name',
        'active_flag',
        'user_no',
        'memo',
        'delivdate',
        'inchargeCode',
        'remarks_id',
        'remarks',
        'goods_code',
        'status',
    ];

    /**
     * Scope: Get active budget codes
     */
    public function scopeActive($query)
    {
        return $query->where('active_flag', 1);
    }
}
