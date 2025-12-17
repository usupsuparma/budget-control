<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddBudgetAuthorizer extends Model
{
    protected $table = 'add_budget_authorizer';
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
