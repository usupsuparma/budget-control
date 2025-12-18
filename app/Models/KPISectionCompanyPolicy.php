<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KPISectionCompanyPolicy extends Model
{
    use HasFactory;

    protected $table = 'kpisection_company_policy';

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

    public function kpiSections()
    {
        // kpi_division.year = kpidivision_company_policy.tahun
        return $this->hasMany(\App\Models\KPISection::class, 'year', 'tahun');
    }

    public function scopeForYear($query, $year)
    {
        return $query->where('tahun', $year);
    }

}
