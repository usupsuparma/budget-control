<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PowerComponents\LivewirePowerGrid\Concerns\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class Employee extends Authenticatable
{
    use Notifiable, HasRoles, SoftDeletes;

    protected $table = 'employee';
    protected $primaryKey = 'id';
    protected $guarded = [];

    // tambahkan jika kamu punya kolom password
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = ['deleted_at'];

    public function role()
    {
        return $this->belongsTo(Roles::class, 'role_id', 'id');
    }

    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class, 'job_position_id', 'id');
    }

    public function employment()
    {
        return $this->hasOne(Employment::class, 'employee_id', 'employee_id');
    }
}
