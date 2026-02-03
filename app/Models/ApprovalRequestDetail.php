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

    // Alias for request() - for backward compatibility
    public function approvalRequest()
    {
        return $this->request();
    }

    public function employment()
    {
        return $this->belongsTo(Employment::class, 'employment_id');
    }

    // Alias for employment_id as employee_id for easier usage
    public function getEmployeeIdAttribute()
    {
        return $this->employment_id;
    }
    
}
