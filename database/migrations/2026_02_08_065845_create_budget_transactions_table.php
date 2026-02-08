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
        Schema::create('budget_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_workplan_id')->constrained('kpi_workplans')->cascadeOnDelete();
            $table->bigInteger('related_advance_id')->nullable();
            $table->char('mutation_type'); // -- Isi dengan 'D' (Debit) atau 'C' (Credit)
            $table->decimal('amount', 15,2);
            $table->string('category', 50);
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_transactions');
    }
};
