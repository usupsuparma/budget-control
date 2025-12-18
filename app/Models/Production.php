<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Production extends Model
{
    use HasFactory;

    protected $fillable = ['type','production','year'];

    public const TYPES = [
        'MAXIMUM PRODUCTION AMOUNT',
        'PRODUCTION AND SALES BALANCE',
    ];

    public function details()
    {
        return $this->hasMany(ProductionDetail::class);
    }
}
