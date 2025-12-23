<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $table = 'division';
    protected $guarded = [];
    protected $fillable = [
        'director_id',
        'name',
        'status',
    ];

    public function director()
    {
        return $this->belongsTo(Director::class, 'director_id');
    }

    /**
     * Relasi ke KPI Division
     * division.id → kpi_division.division_id
     */
    public function kpiDivisions()
    {
        return $this->hasMany(KpiDivision::class, 'division_id');
    }
}
