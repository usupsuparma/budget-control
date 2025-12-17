<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'transaction_date',
        'user_id',
        'user_name',
        'unit_id',
        'unit_name',
        'job_level_id',
        'job_position_id',
        'program_id',
        'purpose',
        'estimated_amount',
        'actual_amount',
        'urgency',
        'status', // 0 = Submission 1 = Approved parent 2 = Approve finance 3 = Approve Division 4= Approve Finance Director 5= Approve President Director 6= Rejected 7 = Pain 8=Complete -1 = cancel
    ];

    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }

    public function historyApprovals()
    {
        return $this->hasMany(TransactionHistoryApproval::class, 'transaction_id');
    }
}
