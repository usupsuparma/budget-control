<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionApprovalLog extends Model
{
    protected $fillable = [
        'transaction_id',
        'approval_id',
        'action',
        'actor_id',
        'actor_name',
        'from_status',
        'to_status',
        'notes',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'transaction_id' => 'integer',
        'approval_id' => 'integer',
        'actor_id' => 'integer',
        'from_status' => 'integer',
        'to_status' => 'integer',
        'metadata' => 'array',
    ];

    public $timestamps = true;

    // Relationships
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function approval()
    {
        return $this->belongsTo(TransactionApproval::class, 'approval_id');
    }

    // Helper method
    public function getActionLabel()
    {
        return match($this->action) {
            'create' => 'Created',
            'approve' => 'Approved',
            'reject' => 'Rejected',
            'delegate' => 'Delegated',
            'escalate' => 'Escalated',
            'comment' => 'Commented',
            default => ucfirst($this->action),
        };
    }
}
