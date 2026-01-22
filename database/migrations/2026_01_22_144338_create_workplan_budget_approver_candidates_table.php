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
        Schema::create('workplan_budget_approver', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workplan_budget_item_id')
                ->constrained('workplan_budget_items') 
                ->onDelete('cascade');
            $table->string('verifier_id', 50);
            $table->boolean('is_executor')->default(false);
            $table->timestamps();
            // index untuk mempercepat pencarian berdasarkan workplan_budget_item_id
            $table->index('workplan_budget_item_id');
            $table->unique(['workplan_budget_item_id', 'verifier_id'], 'unique_approver_per_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workplan_budget_approver');
    }
};
