<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employment extends Model
{
    use SoftDeletes;

    protected $table = 'employment';

    protected $guarded = [];

    protected $dates = ['deleted_at', 'join_date'];

    protected $casts = [
        'join_date' => 'date',
    ];

    protected $fillable = [
        'employee_id', // ini NIP (Nomor Induk Pegawai)
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
        'join_date',
        'status',
    ];

    public function employee()
    {
        // employment.employee_id references employee.employee_id (NIP)
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function jobLevel()
    {
        return $this->belongsTo(JobLevel::class, 'job_level_id', 'id');
    }

    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class, 'job_position_id', 'id');
    }

    public function uppline()
    {
        return $this->belongsTo(Employee::class, 'uppline_id', 'id');
    }

    /**
     * Get uppline's employment (for recursive uppline chain)
     * Note: uppline_id references Employee.id
     */
    public function upplineEmployment()
    {
        return $this->hasOneThrough(
            Employment::class,      // Final model
            Employee::class,        // Intermediate model
            'id',                   // FK on Employee (employee.id)
            'employee_id',          // FK on Employment (employment.employee_id)
            'uppline_id',           // Local key on this Employment (uppline_id points to Employee.id)
            'employee_id'           // Local key on Employee (employee.employee_id)
        );
    }
}
