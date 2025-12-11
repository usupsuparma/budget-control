<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetCodes extends Model
{
    protected $table = 'budget_code';
    protected $guarded = [];

    // public function remark()
    // {
    //     return $this->belongsTo(Remarks::class, 'remarks_id');
    // }
}
