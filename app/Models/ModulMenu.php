<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModulMenu extends Model
{
    protected $table = 'modul_menu';
    protected $fillable = [
        'modul_name',
        'menu_name',

    ];
    protected $guarded = [];
}
