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
        Schema::table('area', function (Blueprint $table) {
            $table->renamecolumn('country', 'continent_province');
            $table->renamecolumn('province', 'country_city');
            $table->renamecolumn('city', 'other');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('area', function (Blueprint $table) {
            //
        });
    }
};
