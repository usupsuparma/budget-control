<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Employee extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $table = 'employee';
    protected $primaryKey = 'id';
    protected $guarded = [];

    // tambahkan jika kamu punya kolom password
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
