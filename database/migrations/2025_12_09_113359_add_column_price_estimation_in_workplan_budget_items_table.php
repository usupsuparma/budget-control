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
        Schema::table('workplan_budget_items', function (Blueprint $table) {
            $table->unsignedBigInteger('price_estimation')->after('status')->nullable();
            $table->string('price_estimation_description', 255)->after('price_estimation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workplan_budget_items', function (Blueprint $table) {
            $table->dropColumn('price_estimation');
            $table->dropColumn('price_estimation_description');
        });
    }
};
