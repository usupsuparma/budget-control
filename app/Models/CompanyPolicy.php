<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyPolicy extends Model
{
    protected $table = 'company_policy';

    protected $fillable = [
        'tahun',
        'nama_dokumen',
        'file_path',
    ];

    public function details()
    {
        return $this->hasMany(CompanyPolicyDetail::class);
    }
}
