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

    public function scopeIsKpiSection($query)
    {
        return $query->where('kpi_type', 'section');
    }

    public function scopeIsKpiDepartment($query)
    {
        return $query->where('kpi_type', 'department');
    }

    /**
     * Scope to filter workplans by Division IDs.
     *
     * Covers both kpi_type = 'department' (via kpiDepartment → department → division_id)
     * and kpi_type = 'section' (via kpiSection → section → department → division_id).
     *
     * If $divisionIds is empty, the scope returns no records (strict security — fail closed).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $divisionIds  List of Division IDs the user has access to
     */
    public function scopeWhereDivisionIn($query, array $divisionIds)
    {
        if (empty($divisionIds)) {
            // Jika tidak ada divisi ditemukan, jangan tampilkan apapun (fail closed)
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($divisionIds) {
            // Workplan tipe 'department': filter via kpiDepartment → department → division_id
            $q->whereHas('kpiDepartment.department', function ($subQ) use ($divisionIds) {
                $subQ->whereIn('division_id', $divisionIds);
            })
            // Workplan tipe 'section': filter via kpiSection → section → department → division_id
            ->orWhereHas('kpiSection.section.department', function ($subQ) use ($divisionIds) {
                $subQ->whereIn('division_id', $divisionIds);
            });
        });
    }

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