<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ModulMenu extends Model
{
    use SoftDeletes;

    protected $table = 'modul_menu';
    protected $fillable = [
        'modul_name',
        'menu_name',

    ];
    protected $guarded = [];

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'modul_menu', 'id');
    }
}
