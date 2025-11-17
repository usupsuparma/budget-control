<?php

namespace App\Helpers;

use Spatie\Permission\Models\Permission;

class PermissionHelper
{
    public static function registerMenuPermission($name)
    {
        $permissions = [
            "$name.view",
            "$name.create",
            "$name.edit",
            "$name.delete"
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
    }
}
