<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KPIDepartmentCompanyPolicy extends Model
{
    use HasFactory;

    protected $table = 'kpidepartment_company_policy';

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

    protected $casts = [
        'tahun' => 'integer',
    ];

    // join berdasarkan tahun (kpi_department.year = kpidepartment_company_policy.tahun)
    public function kpiDepartments()
    {
        return $this->hasMany(KPIDepartement::class, 'year', 'tahun');
    }

    public function scopeForYear($query, $year)
    {
        return $query->where('tahun', $year);
    }
}
