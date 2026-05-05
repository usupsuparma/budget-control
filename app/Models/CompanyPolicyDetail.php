<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CompanyPolicy;

class CompanyPolicyDetail extends Model
{
    protected $table = 'company_policy_detail';

    protected $fillable = [
        'company_policy_id',
        'strategic_goal',
        'description',
        'target',
        'strategic_goal_id',
        'description_id',
    ];

    public function dokumen()
    {
        return $this->belongsTo(CompanyPolicy::class, 'company_policy_id');
    }
}
