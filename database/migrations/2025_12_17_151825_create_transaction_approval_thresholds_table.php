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
        Schema::create('transaction_approval_thresholds', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_amount', 15, 2)->default(0)->comment('Nominal minimum');
            $table->decimal('max_amount', 15, 2)->comment('Nominal maksimum');
            $table->tinyInteger('approval_sequence')->comment('Jumlah level approval yang diperlukan');
            $table->json('required_levels')->comment('Array level yang harus approve: [1,2,3]');
            $table->string('description')->nullable()->comment('Deskripsi threshold');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['min_amount', 'max_amount'], 'idx_amount_range');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_approval_thresholds');
    }
};
