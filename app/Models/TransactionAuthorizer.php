<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionAuthorizer extends Model
{
    protected $table = 'transaction_authorizer';
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
