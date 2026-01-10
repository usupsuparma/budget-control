<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalFlowDetail extends Model
{
    protected $table = "approval_flow_details";
    protected $fillable = [
        "template_id",
        "level_sequence",
        "employment_id",
        "threshold_amount",
        "is_required",
    ];

    protected $casts = [
        "threshold_amount" => "integer",
        "is_required" => "boolean",
    ];  

    public function template()
    {
        return $this->belongsTo(ApprovalFlowTemplate::class, 'template_id');
    }

    public function employment()
    {
        return $this->belongsTo(Employment::class, 'employment_id');
    }
}
