<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{

    protected $fillable = [
        'name',
        'guard_name',
        'modul_menu',
        'modul_menu_name'
    ];

    /**
     * Relationship to ModulMenu
     * Permission belongs to ModulMenu (modul_menu field)
     */
    public function modul()
    {
        return $this->belongsTo(ModulMenu::class, 'modul_menu', 'id');
    }
}
