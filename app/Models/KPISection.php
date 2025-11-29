<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KPISection extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kpi_section';

    protected $fillable = [
        'kpi_department_id',
        'section_id',
        'year',
        'section_goals',
        'activities',
        'target_section',
        'duration_days',
        'schedule_start',
        'schedule_end',
        'jan','feb','mar','apr','may','jun',
        'jul','aug','sep','oct','nov','dec',
        'revenue_cost',
        'unit_id',
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

    public function kpiDepartment()
    {
        return $this->belongsTo(KpiDepartment::class, 'kpi_department_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function workplans()
    {
        return $this->hasMany(KPIWorkPlan::class, 'kpi_id')
            ->where('kpi_type', 'section');
    }
}
