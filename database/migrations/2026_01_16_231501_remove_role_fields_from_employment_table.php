<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Removes legacy role_id and role_name columns from employment table.
     * Role management is now handled by Spatie Laravel Permission via Employee model.
     */
    public function up(): void
    {
        Schema::table('employment', function (Blueprint $table) {
            $table->dropColumn(['role_id', 'role_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employment', function (Blueprint $table) {
            $table->integer('role_id')->nullable()->after('employment_status');
            $table->string('role_name', 100)->nullable()->after('role_id');
        });
    }
};
