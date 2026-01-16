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
        'employee_id', // ini NIP (Nomor Induk Pegawai)
        'email',
        'password',
        'remember_token',
        'first_name',
        'last_name',
        'birth_year',
        'phone',
        'job_position_id',
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

    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class, 'job_position_id', 'id');
    }

    public function employment()
    {
        return $this->hasOne(Employment::class, 'employee_id', 'employee_id');
    }
}
