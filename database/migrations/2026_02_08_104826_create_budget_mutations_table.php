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
        Schema::create('budget_mutations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workplan_budget_item_id');
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('transaction_detail_id');
            $table->unsignedBigInteger('transaction_lpj_submission_id')->nullable();

            $table->enum('mutation_type', ['D', 'C'])->comment('D=Debit (Keluar), C=Credit (Masuk/Refund)');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('category', 50)->comment('CASH_ADVANCE, LPJ_REFUND, LPJ_REIMBURSE');
            $table->text('description')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Foreign keys
            $table->foreign('workplan_budget_item_id')->references('id')->on('workplan_budget_items');
            $table->foreign('transaction_id')->references('id')->on('transactions');
            $table->foreign('transaction_detail_id')->references('id')->on('transaction_details');
            $table->foreign('transaction_lpj_submission_id')->references('id')->on('transaction_lpj_submissions');

            // Indexes for query performance
            $table->index(['workplan_budget_item_id', 'mutation_type'], 'idx_budget_item_mutation_type');
            $table->index('transaction_id', 'idx_transaction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_mutations');
    }
};
