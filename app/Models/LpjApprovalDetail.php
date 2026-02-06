<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LpjApprovalDetail extends Model
{
    protected $table = 'lpj_approval_details';

    protected $fillable = [
        'lpj_submission_id',
        'employment_id',
        'level_sequence',
        'status',
        'notes',
        'actioned_at',
    ];

    protected $casts = [
        'actioned_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get the LPJ submission this detail belongs to.
     */
    public function lpjSubmission()
    {
        return $this->belongsTo(TransactionLpjSubmission::class, 'lpj_submission_id');
    }

    /**
     * Get the employment (approver).
     */
    public function employment()
    {
        return $this->belongsTo(Employment::class, 'employment_id');
    }

    /**
     * Check if this detail is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if this detail is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if this detail is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Get status label.
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default => 'Unknown',
        };
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'secondary',
        };
    }
}
