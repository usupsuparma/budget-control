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
            $table->dropColumn('unit');
            $table->unsignedInteger('unit_id')->nullable()->after('cons_rate');
            $table->string('unit_name')->nullable()->after('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workplan_budget_items', function (Blueprint $table) {
            $table->string('unit')->nullable()->after('cons_rate');
            $table->dropColumn('unit_id');
            $table->dropColumn('unit_name');
        });
    }
};
