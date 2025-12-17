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
        'status',
        'threshold_id',
        'current_approval_level',
        'required_approval_levels',
        'approval_completed_at',
        'rejection_reason',
    ];

   // Status constants
    const STATUS_PENDING = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_APPROVED = 2;
    const STATUS_REJECTED = 3;
    const STATUS_CANCELLED = 4;

    // Relationships
    public function threshold()
    {
        return $this->belongsTo(TransactionApprovalThreshold::class, 'threshold_id');
    }

    public function approvals()
    {
        return $this->hasMany(TransactionApproval::class, 'transaction_id')
                    ->orderBy('sequence_order');
    }

    public function pendingApprovals()
    {
        return $this->hasMany(TransactionApproval::class, 'transaction_id')
                    ->where('status', TransactionApproval::STATUS_PENDING)
                    ->orderBy('sequence_order');
    }

    public function nextApprover()
    {
        return $this->hasOne(TransactionApproval::class, 'transaction_id')
                    ->where('status', TransactionApproval::STATUS_PENDING)
                    ->orderBy('sequence_order')
                    ->oldest();
    }

    public function logs()
    {
        return $this->hasMany(TransactionApprovalLog::class, 'transaction_id')
                    ->orderBy('created_at', 'desc');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    // Helper methods
    public function isFullyApproved()
    {
        return $this->current_approval_level >= $this->required_approval_levels;
    }

    public function getApprovalProgress()
    {
        if ($this->required_approval_levels == 0) {
            return 0;
        }
        return round(($this->current_approval_level / $this->required_approval_levels) * 100);
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown',
        };
    }
}
