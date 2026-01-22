<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkplanBudgetApprover extends Model
{
    // Tabel snapshot kandidat verifikator anggaran
    protected $table = 'workplan_budget_approver';
    
    protected $fillable = [
        'workplan_budget_item_id',
        'verifier_id',
        'is_executor',
    ];

    protected $casts = [
        'is_executor' => 'boolean',
    ];

    public function workplanBudgetItem()
    {
        return $this->belongsTo(WorkplanBudgetItem::class, 'workplan_budget_item_id');
    }

    /**
     * Get verifier employee through employee_id
     */
    public function verifier()
    {
        return $this->belongsTo(Employee::class, 'verifier_id', 'employee_id');
    }

    /**
     * Get verifier employment (for job position info)
     */
    public function verifierEmployment()
    {
        return $this->belongsTo(Employment::class, 'verifier_id', 'employee_id');
    }
}
