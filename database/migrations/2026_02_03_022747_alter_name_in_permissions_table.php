<?php

use App\Models\ModulMenu;
use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $modul = ModulMenu::where([
            'modul_name' => 'Transaction',
            'menu_name' => 'Approval Submission',
        ])->first();

        if (!$modul) {
            return; // stop kalau data tidak ada
        }

        Permission::updateOrCreate(
            [
                'modul_menu' => $modul->id,
                'name' => 'transaction.approval.view',
            ],
            [
                'modul_menu' => $modul->id,
                'name' => 'transaction.approval.view',
                'modul_menu_name' => $modul->menu_name,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            //
        });
    }
};
