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
            // The original migration incorrectly referenced budget_codes.code
            // (a table not in use). The actual table is budget_code with column
            // budget_code (no unique constraint), so this field is a plain string
            // lookup field, not a FK.
            $table->dropForeign('workplan_budget_items_budget_code_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We intentionally do not restore the broken FK constraint.
    }
};
