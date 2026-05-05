<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CompanyPolicyDetail;

class CompanyPolicy extends Model
{
    protected $table = 'company_policy';

    protected $fillable = [
        'tahun',
        'nama_dokumen',
        'file_path',
        'header',
        'contents_en',
        'contents_id',
        'prologue_en',
        'prologue_id',
        'closing_en',
        'closing_id',
        'signature',
    ];

    public function details()
    {
        return $this->hasMany(CompanyPolicyDetail::class, 'company_policy_id');
    }
}
