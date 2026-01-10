<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalModule extends Model
{
    //
    protected $table = "approval_modules";
    protected $fillable = [
        "module_name",
        "table_name",
        "is_active",
    ];

    protected $casts = [
        "is_active" => "boolean",
    ];

    public function templates()
    {
        return $this->hasMany(ApprovalFlowTemplate::class, 'module_id');
    }

    
}
