<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Director extends Model
{
    protected $table = 'director';
    protected $guarded = [];
    protected $fillable = [
        'name',
        'code',
        'status',
        'structure_id',
    ];

    /**
     * Divisions under this director
     */
    public function divisions()
    {
        return $this->hasMany(\App\Models\Division::class, 'director_id');
    }
}
