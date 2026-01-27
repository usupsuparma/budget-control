<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class Employee extends Authenticatable
{
    use HasRoles, Notifiable, SoftDeletes;

    protected $table = 'employee';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $fillable = [
        'employee_code', // NIP (Nomor Induk Pegawai) - format: EMP-YYYYMMDD-XXXX
        'email',
        'password',
        'remember_token',
        'first_name',
        'last_name',
        'birth_year',
        'birth_date',
        'phone',
        'status',
    ];

    protected $appends = [
        'name',
    ];

    // membuat agar name tampil hasil dari first_name dan last_name
    public function getNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    // tambahkan jika kamu punya kolom password
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Get primary role name for display
     * Uses Spatie Laravel Permission
     */
    public function getPrimaryRoleName(): string
    {
        return $this->roles->first()?->name ?? 'No Role';
    }

    /**
     * Get primary role ID (from Spatie)
     */
    public function getPrimaryRoleId(): ?int
    {
        return $this->roles->first()?->id;
    }

    /**
     * Get job position via employment relationship.
     * Since job_position_id is stored in employment table, not employee table.
     */
    public function jobPosition()
    {
        return $this->hasOneThrough(
            JobPosition::class,
            Employment::class,
            'employee_id',      // FK on employment table
            'id',               // PK on job_position table
            'id',               // PK on employee table
            'job_position_id'   // FK on employment table
        );
    }

    /**
     * Get employee's employment record.
     * employment.employee_id (FK) references employee.id (PK)
     */
    public function employment()
    {
        return $this->hasOne(Employment::class, 'employee_id', 'id');
    }
}
