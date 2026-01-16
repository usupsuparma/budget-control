<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Removes legacy role_id column from employee table.
     * Role management is now handled by Spatie Laravel Permission.
     */
    public function up(): void
    {
        Schema::table('employee', function (Blueprint $table) {
            $table->dropColumn('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee', function (Blueprint $table) {
            $table->integer('role_id')->nullable()->after('last_name');
        });
    }
};
