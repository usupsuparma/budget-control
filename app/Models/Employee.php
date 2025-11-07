<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use Notifiable;

    protected $table = 'employee';
    protected $primaryKey = 'id';
    protected $guarded = [];

    // tambahkan jika kamu punya kolom password
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
