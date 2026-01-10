<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employment extends Model
{
    use SoftDeletes;

    protected $table = 'employment';

    protected $guarded = [];
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'employee_id',
        'organization_id',
        'organization_name',
        'job_level_id',
        'job_level_name',
        'job_position_id',
        'job_position_name',
        'uppline_id',
        'uppline_id_name',
        'employment_status',
        'role_id',
        'role_name',
        'status',
    ];

    public function employee()
    {
        // employment.employee_id references employee.id (primary key)
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
