<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KPIWorkPlan extends Model
{
    use SoftDeletes;

    protected $table = 'kpi_workplans';

    protected $fillable = [
        'kpi_type', //enum('department', 'section')
        'kpi_id',
        'year',
        'activity',
        'duration_days',
        'schedule_start',
        'schedule_end',
        'plan_jan', 'plan_feb', 'plan_mar', 'plan_apr', 'plan_may', 'plan_jun',
        'plan_jul', 'plan_aug', 'plan_sep', 'plan_oct', 'plan_nov', 'plan_dec',
        'budget',
        'real_jan', 'real_feb', 'real_mar', 'real_apr', 'real_may', 'real_jun',
        'real_jul', 'real_aug', 'real_sep', 'real_oct', 'real_nov', 'real_dec',
        'status',
        'approved_by',
        'approved_at',
        'description',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'duration_days' => 'integer',
        'schedule_start' => 'date',
        'schedule_end' => 'date',
        'plan_jan' => 'boolean',
        'plan_feb' => 'boolean',
        'plan_mar' => 'boolean',
        'plan_apr' => 'boolean',
        'plan_may' => 'boolean',
        'plan_jun' => 'boolean',
        'plan_jul' => 'boolean',
        'plan_aug' => 'boolean',
        'plan_sep' => 'boolean',
        'plan_oct' => 'boolean',
        'plan_nov' => 'boolean',
        'plan_dec' => 'boolean',
        'budget' => 'decimal:2',
        'real_jan' => 'boolean',
        'real_feb' => 'boolean',
        'real_mar' => 'boolean',
        'real_apr' => 'boolean',
        'real_may' => 'boolean',
        'real_jun' => 'boolean',
        'real_jul' => 'boolean',
        'real_aug' => 'boolean',
        'real_sep' => 'boolean',
        'real_oct' => 'boolean',
        'real_nov' => 'boolean',
        'real_dec' => 'boolean',
        'approved_at' => 'datetime',
    ];

    // Polymorphic relationship
    public function kpi()
    {
        return $this->morphTo(__FUNCTION__, 'kpi_type', 'kpi_id');
    }

    // Explicit relationships
    public function kpiDepartment()
    {
        return $this->belongsTo(KPIDepartment::class, 'kpi_id');
    }

    public function kpiSection()
    {
        return $this->belongsTo(KPISection::class, 'kpi_id');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    // Helper methods
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    // Get planning months as array
    public function getPlanningMonths()
    {
        return [
            'jan' => $this->plan_jan,
            'feb' => $this->plan_feb,
            'mar' => $this->plan_mar,
            'apr' => $this->plan_apr,
            'may' => $this->plan_may,
            'jun' => $this->plan_jun,
            'jul' => $this->plan_jul,
            'aug' => $this->plan_aug,
            'sep' => $this->plan_sep,
            'oct' => $this->plan_oct,
            'nov' => $this->plan_nov,
            'dec' => $this->plan_dec,
        ];
    }

    // Get realization months as array
    public function getRealizationMonths()
    {
        return [
            'jan' => $this->real_jan,
            'feb' => $this->real_feb,
            'mar' => $this->real_mar,
            'apr' => $this->real_apr,
            'may' => $this->real_may,
            'jun' => $this->real_jun,
            'jul' => $this->real_jul,
            'aug' => $this->real_aug,
            'sep' => $this->real_sep,
            'oct' => $this->real_oct,
            'nov' => $this->real_nov,
            'dec' => $this->real_dec,
        ];
    }

    // Calculate realization percentage
    public function getRealizationPercentage()
    {
        $planMonths = array_filter($this->getPlanningMonths());
        $realMonths = array_filter($this->getRealizationMonths());
        
        if (count($planMonths) === 0) {
            return 0;
        }
        
        return round((count($realMonths) / count($planMonths)) * 100, 2);
    }

    // Relationships
    public function budgetItems()
    {
        return $this->hasMany(WorkplanBudgetItem::class, 'kpi_workplan_id');
    }

    // Calculate total budget from budget items
    public function calculateTotalBudget()
    {
        return $this->budgetItems()->sum('total');
    }

    // Update budget from items
    public function updateBudgetFromItems()
    {
        $this->budget = $this->calculateTotalBudget();
        $this->save();
        return $this->budget;
    }
}