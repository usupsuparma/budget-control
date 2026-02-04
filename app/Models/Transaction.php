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
        'status_approval',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'estimated_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'approval_completed_at' => 'datetime',
    ];

    // Status Transaction constants 0:Submission|1:Approved Parent|2:Approved Finance|3:Approved Division|4:Approved Finance Director|5:Approved President Director|6:Rejected|7:Paid|8:Complete|-1:Cancelled
    const STATUS_SUBMISSION = 0;
    const STATUS_APPROVED_PARENT = 1;
    const STATUS_APPROVED_FINANCE = 2;
    const STATUS_APPROVED_DIVISION = 3;
    const STATUS_APPROVED_FINANCE_DIRECTOR = 4;
    const STATUS_APPROVED_PRESIDENT_DIRECTOR = 5;
    const STATUS_REJECTED = 6;
    const STATUS_PAID = 7;
    const STATUS_COMPLETE = 8;
    const STATUS_CANCELLED = -1;

    // Legacy transaction workflow status constants (still in use for 'status' field)
    const STATUS_PENDING = 0;  // Used for draft/pending transactions
    // const STATUS_IN_PROGRESS = 1;  // Not used anymore
    // const STATUS_APPROVED = 2;  // Not used anymore
    // const STATUS_REJECTED = 3;  // Not used anymore
    // Note: STATUS_CANCELLED = -1 is defined above

    // Approval Status constants (dynamic approval system for 'status_approval' field)
    const APPROVAL_STATUS_PENDING = 'pending';
    const APPROVAL_STATUS_IN_PROGRESS = 'in_progress';
    const APPROVAL_STATUS_APPROVED = 'approved';
    const APPROVAL_STATUS_REJECTED = 'rejected';
    const APPROVAL_STATUS_CANCELLED = 'cancelled';

    // Urgency constants
    const URGENCY_LOW = 'low';
    const URGENCY_MEDIUM = 'medium';
    const URGENCY_HIGH = 'high';

    // Relationships
    /**
     * Get the approval request for this transaction (dynamic approval system).
     */
    public function approvalRequest()
    {
        return $this->hasOne(ApprovalRequest::class, 'reference_id')
            ->whereHas('module', fn($q) => $q->where('table_name', 'transactions'));
    }

    /**
     * Get approval request details through approval request.
     */
    public function approvalRequestDetails()
    {
        return $this->hasManyThrough(
            ApprovalRequestDetail::class,
            ApprovalRequest::class,
            'reference_id', // Foreign key on ApprovalRequest
            'request_id', // Foreign key on ApprovalRequestDetail
            'id', // Local key on Transaction
            'id' // Local key on ApprovalRequest
        )->whereHas('request.module', fn($q) => $q->where('table_name', 'transactions'));
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }

    public function user()
    {
        return $this->belongsTo(Employee::class, 'user_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function jobLevel()
    {
        return $this->belongsTo(JobLevel::class, 'job_level_id');
    }

    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class, 'job_position_id');
    }

    // Scopes
    

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByYear($query, $year)
    {
        return $query->whereYear('transaction_date', $year);
    }

    public function scopeByMonth($query, $month)
    {
        return $query->whereMonth('transaction_date', $month);
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


    /**
     * Get approval status label (dynamic approval system)
     */
    public function getApprovalStatusLabel(): string
    {
        return match($this->status_approval) {
            self::APPROVAL_STATUS_PENDING => 'Pending Approval',
            self::APPROVAL_STATUS_IN_PROGRESS => 'In Progress',
            self::APPROVAL_STATUS_APPROVED => 'Approved',
            self::APPROVAL_STATUS_REJECTED => 'Rejected',
            self::APPROVAL_STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Get approval status badge class (dynamic approval system)
     */
    public function getApprovalStatusBadgeClass(): string
    {
        return match($this->status_approval) {
            self::APPROVAL_STATUS_PENDING => 'warning',
            self::APPROVAL_STATUS_IN_PROGRESS => 'info',
            self::APPROVAL_STATUS_APPROVED => 'success',
            self::APPROVAL_STATUS_REJECTED => 'danger',
            self::APPROVAL_STATUS_CANCELLED => 'secondary',
            default => 'light',
        };
    }

    /**
     * Check if transaction is pending approval
     */
    public function isApprovalPending(): bool
    {
        return $this->status_approval === self::APPROVAL_STATUS_PENDING;
    }

    /**
     * Check if transaction approval is in progress
     */
    public function isApprovalInProgress(): bool
    {
        return $this->status_approval === self::APPROVAL_STATUS_IN_PROGRESS;
    }

    /**
     * Check if transaction is fully approved
     */
    public function isApprovalApproved(): bool
    {
        return $this->status_approval === self::APPROVAL_STATUS_APPROVED;
    }

    /**
     * Check if transaction approval was rejected
     */
    public function isApprovalRejected(): bool
    {
        return $this->status_approval === self::APPROVAL_STATUS_REJECTED;
    }

    public function getUrgencyLabel()
    {
        return match($this->urgency) {
            self::URGENCY_LOW => 'Low',
            self::URGENCY_MEDIUM => 'Medium',
            self::URGENCY_HIGH => 'High',
            default => 'Unknown',
        };
    }

    public function getUrgencyBadgeClass()
    {
        return match($this->urgency) {
            self::URGENCY_LOW => 'success',
            self::URGENCY_MEDIUM => 'warning',
            self::URGENCY_HIGH => 'danger',
            default => 'light',
        };
    }
}
