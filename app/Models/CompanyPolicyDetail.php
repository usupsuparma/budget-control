<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyPolicyDetail extends Model
{
    protected $table = 'company_policy_detail';

    protected $fillable = [
        'company_policy_id',
        'strategic_goal',
        'description',
        'target'
    ];

    public function dokumen()
    {
        return $this->belongsTo(CompanyPolicy::class);
    }
}
