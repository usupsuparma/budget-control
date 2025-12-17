<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionAuthorizer extends Model
{
    use SoftDeletes;

    protected $table = 'transaction_authorizer';

    protected $fillable = [
        'level_number',
        'authorizer_name',
        'employee_id',
        'status',
        'position_code',
        'approval_level',
        'max_approval_amount',
        'can_override',
        'priority_order',
    ];

    protected $casts = [
        'level_number' => 'integer',
        'employee_id' => 'integer',
        'status' => 'integer',
        'approval_level' => 'integer',
        'max_approval_amount' => 'decimal:2',
        'can_override' => 'boolean',
        'priority_order' => 'integer',
    ];

    // Scope untuk get active authorizers
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    // Scope untuk get authorizer by level
    public function scopeByLevel($query, $level)
    {
        return $query->where('approval_level', $level);
    }

    // Scope untuk get authorizer dengan urutan prioritas
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority_order', 'asc');
    }

    // Check apakah authorizer bisa approve amount tertentu
    public function canApproveAmount($amount)
    {
        return $this->max_approval_amount >= $amount;
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
