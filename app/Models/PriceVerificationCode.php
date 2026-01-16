<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceVerificationCode extends Model
{
    protected $table = 'price_verification_code';
    protected $guarded = [];

    public function verificator()
    {
        return $this->belongsTo(PriceVerification::class, 'price_verification_id');
    }
}
