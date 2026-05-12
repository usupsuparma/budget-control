<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KPIDivision extends Model
{
    use HasFactory;

    // karena nama tabel tidak jamak (default Laravel: kpi_divisions)
    protected $table = 'kpi_division';

    /**
     * Kolom yang boleh di-*mass assign* (fillable).
     */
    protected $fillable = [
        'company_policy_detail_id',
        'division_id',
        'year',
        'no',
        'division_goals',
        'target_division',
        'duration_days',
        'schedule_start',
        'schedule_end',
        'jan',
        'feb',
        'mar',
        'apr',
        'may',
        'jun',
        'jul',
        'aug',
        'sep',
        'oct',
        'nov',
        'dec',
        'revenue_cost',
        'pic',
        'description',
    ];

    /**
     * Casting tipe data.
     */
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

    /**
     * Relasi ke CompanyPolicy
     * kpi_division.company_policy_id → company_policy.id
     */
    public function companyPolicy()
    {
        return $this->belongsTo(CompanyPolicyDetail::class, 'company_policy_detail_id');
    }

    /**
     * Relasi ke Division
     * kpi_division.division_id → division.id
     */
    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    /**
     * Scope helper: filter berdasarkan tahun.
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope helper: filter berdasarkan division.
     */
    public function scopeForDivision($query, $divisionId)
    {
        return $query->where('division_id', $divisionId);
    }

    public function kpiDepartments()
    {
        return $this->hasMany(KPIDepartment::class, 'kpi_division_id');
    }

    public function companyPolicyByDivision()
    {
        // kpi_division.year -> kpidivision_company_policy.tahun
        return $this->belongsTo(KPIDivisionCompanyPolicy::class, 'year', 'tahun');
    }
}
