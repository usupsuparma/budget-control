<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkplanBudgetVerification extends Model
{
    //
    protected $table = 'workplan_budget_verifications';
    protected $fillable = [
        'workplan_budget_item_id',
        'verifier_id',
        'submitted_price_estimation',
        'verified_price_total',
        'notes',
    ];

    public function workplanBudgetItem()
    {
        return $this->belongsTo(WorkplanBudgetItem::class, 'workplan_budget_item_id');
    }

    public function verifier()
    {
        return $this->belongsTo(Employee::class, 'verifier_id', 'employee_id');
    }
}
