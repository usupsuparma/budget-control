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
    ];
}
