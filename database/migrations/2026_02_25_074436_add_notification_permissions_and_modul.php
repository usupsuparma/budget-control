<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert Modul Menu for Notifications
        $modulId = DB::table('modul_menu')->insertGetId([
            'modul_name' => 'Settings',
            'menu_name' => 'Notifications',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert Permissions
        $permissions = [
            [
                'name' => 'setting.notification.view',
                'guard_name' => 'web',
                'modul_menu' => $modulId,
                'modul_menu_name' => 'Notification View',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'setting.notification.category.view',
                'guard_name' => 'web',
                'modul_menu' => $modulId,
                'modul_menu_name' => 'Notification Category View',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('permissions')->insert($permissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'setting.notification.view',
            'setting.notification.category.view'
        ])->delete();

        DB::table('modul_menu')->where('modul_name', 'Settings')->where('menu_name', 'Notifications')->delete();
    }
};
