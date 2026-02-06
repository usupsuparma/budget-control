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
        Schema::create('workplan_budget_verifications', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('workplan_budget_item_id')->constrained('workplan_budget_items')->onDelete('cascade');
            $table->unsignedBigInteger('workplan_budget_item_id');
            $table->string('verifier_id', 50);
            $table->unsignedBigInteger('submitted_price_estimation')->comment('harga yang diajukan pada saat pengajuan anggaran');
            $table->unsignedBigInteger('verified_price_total')->comment('harga yang telah diverifikasi oleh verifier');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workplan_budget_verifications');
    }
};
