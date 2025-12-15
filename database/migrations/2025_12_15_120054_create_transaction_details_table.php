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
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('budget_id');
            $table->string('budget_name')->nullable();
            $table->string('goods_service_name')->nullable();
            $table->unsignedBigInteger('balance')->default(0);
            $table->unsignedInteger('estimated_price')->default(0);
            $table->unsignedInteger('estimated_quantity')->default(0);
            $table->unsignedInteger('estimated_total')->default(0);
            $table->unsignedInteger('fix_price')->default(0);
            $table->unsignedInteger('fix_quantity')->default(0);
            $table->unsignedInteger('fix_total')->default(0);
            $table->unsignedInteger('unit_id');
            $table->string('unit_name')->nullable();
            $table->string('remark')->nullable();
            $table->string('urgency')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
