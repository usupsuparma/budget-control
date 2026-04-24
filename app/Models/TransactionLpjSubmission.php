<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class TransactionLpjSubmission extends Model
{
    use SoftDeletes;

    protected $table = 'transaction_lpj_submissions';

    protected $fillable = [
        'transaction_id',
        'submission_date',
        'realization_date',
        'proof_of_payment',
        'status_approval',
        'current_approval_level',
        'total_approval_levels',
        'rejection_reason',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'realization_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected $appends = [
        'proof_of_payment_url',
        'proof_of_payment_name',
        'proof_of_payment_extension',
        'proof_of_payment_mime_type',
        'proof_of_payment_preview_type',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get the transaction this LPJ belongs to.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    /**
     * Get the approval details for this LPJ submission.
     */
    public function approvalDetails()
    {
        return $this->hasMany(LpjApprovalDetail::class, 'lpj_submission_id')
            ->orderBy('level_sequence');
    }

    /**
     * Get the approver who finally approved this LPJ.
     */
    public function finalApprover()
    {
        return $this->belongsTo(Employment::class, 'approved_by');
    }

    public function getProofOfPaymentUrlAttribute(): ?string
    {
        if (! $this->proof_of_payment) {
            return null;
        }

        return route('userSubmission.lpj.proof', $this->id);
    }

    public function getProofOfPaymentNameAttribute(): ?string
    {
        return $this->proof_of_payment ? basename($this->proof_of_payment) : null;
    }

    public function getProofOfPaymentExtensionAttribute(): ?string
    {
        return $this->proof_of_payment ? strtolower(pathinfo($this->proof_of_payment, PATHINFO_EXTENSION)) : null;
    }

    public function getProofOfPaymentMimeTypeAttribute(): ?string
    {
        if (! $this->proof_of_payment || ! Storage::disk('public')->exists($this->proof_of_payment)) {
            return null;
        }

        return Storage::disk('public')->mimeType($this->proof_of_payment);
    }

    public function getProofOfPaymentPreviewTypeAttribute(): ?string
    {
        $extension = $this->proof_of_payment_extension;

        if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            return 'image';
        }

        if ($extension === 'pdf') {
            return 'pdf';
        }

        return $extension ? 'download' : null;
    }

    /**
     * Check if LPJ is pending approval.
     */
    public function isPending(): bool
    {
        return in_array($this->status_approval, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Check if LPJ is approved.
     */
    public function isApproved(): bool
    {
        return $this->status_approval === self::STATUS_APPROVED;
    }

    /**
     * Check if LPJ is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status_approval === self::STATUS_REJECTED;
    }

    /**
     * Get the current pending approval detail.
     */
    public function getCurrentPendingApproval()
    {
        return $this->approvalDetails()
            ->where('status', 'pending')
            ->orderBy('level_sequence')
            ->first();
    }

    /**
     * Get approval progress percentage.
     */
    public function getApprovalProgress(): int
    {
        if ($this->total_approval_levels == 0) {
            return 0;
        }
        return (int) round(($this->current_approval_level / $this->total_approval_levels) * 100);
    }

    /**
     * Get status label.
     */
    public function getStatusLabel(): string
    {
        return match($this->status_approval) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
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
        return match($this->status_approval) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Scope for pending LPJ submissions.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status_approval', [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Scope for approved LPJ submissions.
     */
    public function scopeApproved($query)
    {
        return $query->where('status_approval', self::STATUS_APPROVED);
    }

    /**
     * Scope for rejected LPJ submissions.
     */
    public function scopeRejected($query)
    {
        return $query->where('status_approval', self::STATUS_REJECTED);
    }
}
