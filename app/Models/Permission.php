<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * Relationship to ModulMenu
     * Permission belongs to ModulMenu (modul_menu field)
     */
    public function modul()
    {
        return $this->belongsTo(ModulMenu::class, 'modul_menu', 'id');
    }
}
