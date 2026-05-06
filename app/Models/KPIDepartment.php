<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KPIDepartment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kpi_department';

    protected $fillable = [
        'kpi_division_id',
        'department_id',
        'year',
        'department_goals',
        'department_activities',
        'target_department',
        'duration_days',
        'schedule_start',
        'schedule_end',
        'jan','feb','mar','apr','may','jun',
        'jul','aug','sep','oct','nov','dec',
        'revenue_cost',
        'pic',
        'description',
    ];

    protected $casts = [
        'year'           => 'integer',
        'duration_days'  => 'integer',
        'schedule_start' => 'date',
        'schedule_end'   => 'date',
        'jan' => 'boolean',
        'feb' => 'boolean',
        'mar' => 'boolean',
        'apr' => 'boolean',
        'may' => 'boolean',
        'jun' => 'boolean',
        'jul' => 'boolean',
        'aug' => 'boolean',
        'sep' => 'boolean',
        'oct' => 'boolean',
        'nov' => 'boolean',
        'dec' => 'boolean',
    ];

    public function kpiDivision()
    {
        return $this->belongsTo(KPIDivision::class, 'kpi_division_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function kpiSections()
    {
        return $this->hasMany(KPISection::class, 'kpi_department_id');
    }

    public function workplans()
    {
        return $this->hasMany(KPIWorkPlan::class, 'kpi_id')
            ->where('kpi_type', 'department');
    }
}
