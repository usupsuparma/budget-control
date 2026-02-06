<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated This model is part of the legacy approval system.
 * Use the dynamic approval system (ApprovalRequest, ApprovalRequestDetail) instead.
 * This class will be removed in a future version.
 */
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
