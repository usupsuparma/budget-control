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

    public function jobLevel()
    {
        return $this->belongsTo(JobLevel::class, 'job_level_id');
    }

    /**
     * Get the division/structure that this job position belongs to.
     * job_position.structure_id → division.id
     */
    public function structure()
    {
        return $this->belongsTo(Division::class, 'job_level_id', 'id');
    }
}
