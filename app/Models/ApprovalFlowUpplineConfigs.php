<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalFlowUpplineConfigs extends Model
{
    use SoftDeletes;

    protected $table = "approval_flow_uppline_configs";
    
    protected $fillable = [
        "template_id",
        "division_id",
        "step_sequence",
        "job_level_name",
        "threshold_amount",
    ];

    protected $casts = [
        'step_sequence' => 'integer',
        'template_id' => 'integer',
        'division_id' => 'integer',
        'threshold_amount' => 'integer',
    ];

    // ========== RELATIONSHIPS ==========

    /**
     * Relationship to approval flow template
     */
    public function template()
    {
        return $this->belongsTo(ApprovalFlowTemplate::class, 'template_id');
    }

    /**
     * Relationship to division (nullable)
     */
    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }
}
