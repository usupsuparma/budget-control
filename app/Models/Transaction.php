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

   // Status constants (legacy workflow status)
    const STATUS_PENDING = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_APPROVED = 2;
    const STATUS_REJECTED = 3;
    const STATUS_CANCELLED = 4;

    // Approval Status constants (dynamic approval system)
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
    public function threshold()
    {
        return $this->belongsTo(TransactionApprovalThreshold::class, 'threshold_id');
    }

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

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isInProgress()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeEdited()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeCancelled()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
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

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            default => 'light',
        };
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

    /**
     * Get the next pending approval
     */
    public function getNextPendingApproval()
    {
        return $this->approvals()
            ->where('status', TransactionApproval::STATUS_PENDING)
            ->orderBy('sequence_order')
            ->first();
    }

    /**
     * Get current approver info
     */
    public function getCurrentApproverInfo()
    {
        $nextApproval = $this->getNextPendingApproval();
        
        if ($nextApproval) {
            return [
                'level' => $nextApproval->approval_level,
                'name' => $nextApproval->approver_name,
                'id' => $nextApproval->approver_id,
            ];
        }
        
        return null;
    }

    /**
     * Check if a user can approve this transaction
     */
    public function canBeApprovedBy($userId)
    {
        if ($this->status !== self::STATUS_IN_PROGRESS) {
            return false;
        }

        $nextApproval = $this->getNextPendingApproval();
        
        if (!$nextApproval) {
            return false;
        }

        // Check if user is the assigned approver
        if ($nextApproval->approver_id === $userId) {
            return true;
        }

        // Check if user has override permission
        $authorizer = TransactionAuthorizer::where('employee_id', $userId)
            ->where('can_override', true)
            ->first();

        return $authorizer !== null;
    }
}
