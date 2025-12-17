<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiDivisionCompanyPolicy extends Model
{
    use HasFactory;

    protected $table = 'kpidivision_company_policy';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'tahun',
        'header',
        'contents_en',
        'contents_id',
        'prologue_en',
        'prologue_id',
        'closing_en',
        'closing_id',
        'signature',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'tahun' => 'integer',
    ];

    public function kpiDivisions()
    {
        // kpi_division.year = kpidivision_company_policy.tahun
        return $this->hasMany(\App\Models\KPIDivision::class, 'year', 'tahun');
    }

    public function scopeForYear($query, $year)
    {
        return $query->where('tahun', $year);
    }

}
