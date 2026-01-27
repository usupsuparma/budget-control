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
     * Get verifier employee (verifier_id = FK to employee.id)
     */
    public function verifier()
    {
        return $this->belongsTo(Employee::class, 'verifier_id', 'id');
    }

    /**
     * Get verifier employment through Employee
     * verifier_id -> Employee.id -> Employment.employee_id
     */
    public function verifierEmployment()
    {
        return $this->hasOneThrough(
            Employment::class,
            Employee::class,
            'id',           // FK on Employee (employee.id)
            'employee_id',  // FK on Employment (employment.employee_id -> employee.id)
            'verifier_id',  // Local key on WorkplanBudgetApprover
            'id'            // Local key on Employee (employee.id)
        );
    }
}
