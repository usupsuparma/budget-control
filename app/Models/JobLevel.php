<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobLevel extends Model
{
    protected $table = 'job_level';
    protected $guarded = [];

    protected $fillable = [
        "job_level_name",
        "status",
    ];
}
