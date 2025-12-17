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
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('job_level_id')->nullable()->after('unit_name');
            $table->unsignedBigInteger('job_position_id')->nullable()->after('job_level_id');
            $table->unsignedBigInteger('program_id')->nullable()->after('job_position_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['job_level_id', 'job_position_id', 'program_id']);
        });
    }
};
