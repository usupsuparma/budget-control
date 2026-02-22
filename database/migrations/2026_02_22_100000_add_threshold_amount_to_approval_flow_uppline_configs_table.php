<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add threshold_amount column to approval_flow_uppline_configs table.
     * This allows upline approval to be threshold-based.
     */
    public function up(): void
    {
        Schema::table('approval_flow_uppline_configs', function (Blueprint $table) {
            $table->unsignedBigInteger('threshold_amount')->nullable()->default(0)->after('job_level_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_flow_uppline_configs', function (Blueprint $table) {
            $table->dropColumn('threshold_amount');
        });
    }
};
