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
            $table->enum('category_type', ['Routine', 'Carry Over', 'Turn Around', 'Multi Year'])->default('Routine')->after('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workplan_budget_items', function (Blueprint $table) {
            $table->dropColumn('category_type');
        });
    }
};
