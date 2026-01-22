<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceVerification extends Model
{
    protected $table = 'price_verification';
    protected $guarded = [];
    protected $fillable = [
        'verificator',
        'description'
    ];

    public function codes()
    {
        return $this->hasMany(PriceVerificationCode::class, 'price_verification_id');
    }

    // relasi ke price_verification_user
    public function users()
    {
        return $this->hasMany(PriceVerificationUser::class, 'price_verification_id');
    }
}
