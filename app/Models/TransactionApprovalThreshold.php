<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @deprecated This model is part of the legacy approval system.
 * Use the dynamic approval system with approval_flow_templates and approval_flow_details instead.
 * This class will be removed in a future version.
 */
class TransactionApprovalThreshold extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'min_amount',
        'max_amount',
        'approval_sequence',
        'required_levels',
        'description',
        'is_active',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'approval_sequence' => 'integer',
        'required_levels' => 'array',
        'is_active' => 'boolean',
    ];

    // Scope untuk get threshold berdasarkan amount
    public function scopeForAmount($query, $amount)
    {
        return $query->where('min_amount', '<=', $amount)
            ->where('max_amount', '>=', $amount)
            ->where('is_active', true);
    }

    // Relationship
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'threshold_id');
    }

    public function approvals()
    {
        return $this->hasMany(TransactionApproval::class, 'threshold_id');
    }
}
