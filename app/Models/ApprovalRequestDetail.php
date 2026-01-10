<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequestDetail extends Model
{
    protected $table = "approval_request_details";
    protected $fillable = [
        "request_id",
        "phase",
        "level_sequence",
        "employment_id",
        "employment_name",
        "status",
        "approved_at",
    ];

    protected $casts = [
        "approved_at" => "datetime",
    ];

    public function request()
    {
        return $this->belongsTo(ApprovalRequest::class, 'request_id');
    }

    public function employment()
    {
        return $this->belongsTo(Employment::class, 'employment_id');
    }

    
    
}
