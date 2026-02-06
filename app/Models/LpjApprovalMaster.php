<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LpjApprovalMaster extends Model
{
    protected $table = 'lpj_approval_masters';

    protected $fillable = [
        'employment_id',
        'approval_sequence',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the employment that is the approver.
     */
    public function employment()
    {
        return $this->belongsTo(Employment::class, 'employment_id');
    }

    /**
     * Scope for active approvers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sequence.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('approval_sequence', 'asc');
    }

    /**
     * Get all active approvers in order.
     */
    public static function getActiveApprovers()
    {
        return static::active()->ordered()->with('employment.employee')->get();
    }
}
