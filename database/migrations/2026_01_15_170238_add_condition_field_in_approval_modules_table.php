<?php

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
        Schema::table('approval_modules', function (Blueprint $table) {
            $table->string('condition_field', 50)->nullable()->after('module_name')->comment('Field untuk kondisi approval, misal: total, amount, dsb jika threshold berdasarkan nilai tertentu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_modules', function (Blueprint $table) {
            $table->dropColumn('condition_field');
        });
    }
};
