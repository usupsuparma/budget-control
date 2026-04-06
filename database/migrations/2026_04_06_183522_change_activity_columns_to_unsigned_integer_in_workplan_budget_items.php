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
            $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
            foreach ($months as $month) {
                // Some DBs like MySQL require doctrine/dbal for change() but Laravel 11+ handles it mostly
                $table->unsignedInteger('activity_'.$month)->default(0)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workplan_budget_items', function (Blueprint $table) {
            $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
            foreach ($months as $month) {
                $table->integer('activity_'.$month)->default(0)->change();
            }
        });
    }
};
