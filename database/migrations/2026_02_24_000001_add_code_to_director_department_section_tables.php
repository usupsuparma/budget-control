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
        Schema::table('director', function (Blueprint $table) {
            $table->string('code', 10)->after('name')->nullable();
        });
        Schema::table('department', function (Blueprint $table) {
            $table->string('code', 10)->after('name')->nullable();
        });
        Schema::table('section', function (Blueprint $table) {
            $table->string('code', 10)->after('name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('director', function (Blueprint $table) {
            $table->dropColumn('code');
        });
        Schema::table('department', function (Blueprint $table) {
            $table->dropColumn('code');
        });
        Schema::table('section', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
