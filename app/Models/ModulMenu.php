<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModulMenu extends Model
{
    protected $table = 'modul_menus';
    protected $fillable = [
        'modul_name',
        'menu_name',

    ];
    protected $guarded = [];
}
