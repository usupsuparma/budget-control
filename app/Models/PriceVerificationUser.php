<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceVerificationUser extends Model
{
    protected $table = 'price_verification_user';
    protected $guarded = [];
    protected $fillable = [
        'price_verification_id',
        'job_position_id',
    ];


    public function verificator()
    {
        return $this->belongsTo(PriceVerification::class, 'price_verification_id');
    }

    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class, 'job_position_id');
    }
}
