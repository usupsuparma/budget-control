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
    ];

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }
}
