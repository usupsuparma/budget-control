<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalFlowTemplate extends Model
{
    //
    protected $table = "approval_flow_templates";
    protected $fillable = [
        "module_id",
        "template_name",
        "use_uppline_chain",
        "use_threshold",
        "condition_field",
        "priority",
        "is_active",
    ];

    protected $casts = [
        "use_uppline_chain" => "boolean",
        "use_threshold" => "boolean",
        "is_active" => "boolean",
    ];

    public function module()
    {
        return $this->belongsTo(ApprovalModule::class, 'module_id');
    }

    public function details()
    {
        return $this->hasMany(ApprovalFlowDetail::class, 'template_id');
    }
}
