<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceVerificationCode extends Model
{
    protected $table = 'price_verification_code';
    protected $guarded = [];
    protected $fillable = [
        'price_verification_id',
        'remarks',
        'inchargecode'
    ];

    

    public function verificator()
    {
        return $this->belongsTo(PriceVerification::class, 'price_verification_id');
    }
}
