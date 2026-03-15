<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'department';
    protected $guarded = [];

    protected $fillable = [
        'division_id',
        'name',
        'status',
        'code',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    /**
     * Sections under this department
     */
    public function sections()
    {
        return $this->hasMany(\App\Models\Section::class, 'department_id');
    }
}
