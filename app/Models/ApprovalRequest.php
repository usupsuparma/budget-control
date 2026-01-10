<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    protected $table = "approval_requests";
    protected $fillable = [
        "module_id",
        "reference_id",
        "reference_number",
        "template_id",
        "template_snapshot",
        "status",
        "current_phase",
        "current_level",
        "total_levels",
        "requester_id",
        "requested_at",
        "completed_at",
    ];

    protected $casts = [
        "total_levels" => "integer",
        "requested_at" => "datetime",
        "completed_at" => "datetime",
    ];

    public function module()
    {
        return $this->belongsTo(ApprovalModule::class, 'module_id');
    }
    public function template()
    {
        return $this->belongsTo(ApprovalFlowTemplate::class, 'template_id');
    }
    
}
