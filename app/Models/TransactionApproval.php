<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @deprecated This model is part of the legacy approval system.
 * Use the dynamic approval system (ApprovalRequest, ApprovalRequestDetail) instead.
 * This class will be removed in a future version.
 */
class TransactionApproval extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'approver_id',
        'approver_name',
        'approval_level',
        'threshold_id',
        'is_required',
        'sequence_order',
        'status',
        'approved_at',
        'comments',
        'notified_at',
        'reminder_count',
        'reminder_last_sent',
        'approval_method',
        'ip_address',
    ];

     protected $casts = [
        'transaction_id' => 'integer',
        'approver_id' => 'integer',
        'approval_level' => 'integer',
        'threshold_id' => 'integer',
        'is_required' => 'boolean',
        'sequence_order' => 'integer',
        'status' => 'integer',
        'approved_at' => 'datetime',
        'notified_at' => 'datetime',
        'reminder_count' => 'integer',
        'reminder_last_sent' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;
    const STATUS_SKIPPED = 3;

    
    // Relationships
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function threshold()
    {
        return $this->belongsTo(TransactionApprovalThreshold::class, 'threshold_id');
    }

    public function authorizer()
    {
        return $this->belongsTo(TransactionAuthorizer::class, 'approver_id', 'employee_id');
    }

    public function logs()
    {
        return $this->hasMany(TransactionApprovalLog::class, 'approval_id')
                    ->orderBy('created_at', 'desc');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    // Helper methods
    public function getStatusLabel()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_SKIPPED => 'Skipped',
            default => 'Unknown',
        };
    }

    public function needsReminder()
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        if (!$this->notified_at) {
            return true;
        }

        // Kirim reminder jika sudah 24 jam tidak ada response
        return $this->notified_at->diffInHours(now()) >= 24;
    }
}
