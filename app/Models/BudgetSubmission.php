<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BudgetSubmission extends Model
{
    use SoftDeletes;

    protected $table = 'budget_submissions';
    protected $guarded = [];
    
    protected $fillable = [
        'user_id',
        'division_id',
        'work_plan_id',
        'division_name',
        'submission_date',
        'type', // enum('type', ['add', 'relocation']);
        'budget_account_id',
        'estimation_amount',
        'description',
        'status',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'estimation_amount' => 'integer',
        'status' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(Employee::class, 'user_id');
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'reference_id')
            ->whereHas('module', fn($q) => $q->where('table_name', 'budget_submissions'));
    }

    public function latestApprovalRequest(): HasOne
    {
        return $this->hasOne(ApprovalRequest::class, 'reference_id')
            ->whereHas('module', fn($q) => $q->where('table_name', 'budget_submissions'))
            ->latest('id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function workPlan()
    {
        return $this->belongsTo(KPIWorkPlan::class, 'work_plan_id');
    }

    public function budgetAccount()
    {
        return $this->belongsTo(BudgetCode::class, 'budget_account_id');
    }

    // Scopes
    public function scopeByDivision($query, $divisionId)
    {
        return $query->where('division_id', $divisionId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 0);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 1);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 2);
    }

    // Helper Methods
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            0 => 'Pending',
            1 => 'Approved',
            2 => 'Rejected',
            default => 'Unknown'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            0 => 'warning',
            1 => 'success',
            2 => 'danger',
            default => 'secondary'
        };
    }

    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'add' => 'Add Budget',
            'relocation' => 'Relocation',
            default => $this->type
        };
    }

    public function isPending()
    {
        return $this->status === 0;
    }

    public function hasPendingApproval(): bool
    {
        return $this->latestApprovalRequest && $this->latestApprovalRequest->status === 'pending';
    }

    public function canBeEdited(): bool
    {
        return $this->status === 0 && ! $this->hasPendingApproval();
    }

    public function canBeDeleted(): bool
    {
        return $this->canBeEdited();
    }

    public function getApprovalProgressLabelAttribute(): string
    {
        if ($this->latestApprovalRequest && $this->latestApprovalRequest->status === 'pending') {
            return 'In Approval Process';
        }

        return $this->status_label;
    }

    public function isApproved()
    {
        return $this->status === 1;
    }

    public function isRejected()
    {
        return $this->status === 2;
    }
}
