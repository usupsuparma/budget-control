<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPosition extends Model
{
    protected $table = 'job_position';
    protected $guarded = [];

    protected $fillable = [
        "job_position_name",
        "job_level_id",
        "job_level_name",
        "structure_id",
        "structure_name",
        "status",
    ];
}
