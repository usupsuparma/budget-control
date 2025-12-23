<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionHistoryApproval extends Model
{
    protected $fillable = [
        'transaction_id',
        'user_id',
        'user_name',
        'approval_date',
        'comment',
        'status',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
