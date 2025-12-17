<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
