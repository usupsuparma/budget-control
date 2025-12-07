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
        Schema::create('sales_planning_detail', function (Blueprint $table) {
            $table->id();
            $table->integer('sales_planning_id')->nullable();
            $table->string('month')->nullable();
            $table->string('diff_with_actual')->nullable();
            $table->string('sales_budget')->nullable();

            $table->string('vc')->nullable();
            $table->string('fc')->nullable();

            $table->string('other_cost')->nullable();
            $table->string('trans')->nullable();
            $table->string('pack')->nullable();

            $table->string('total_cost')->nullable();
            $table->string('sales_price')->nullable();
            $table->string('margin')->nullable();

            $table->string('margin_percent')->nullable();
            $table->string('sales_quantity')->nullable();

            $table->string('sales_amount')->nullable();
            $table->string('margin_amount')->nullable();

            $table->string('full_cost')->nullable();
            $table->string('transport_amount')->nullable();
            $table->string('packing_amount')->nullable();

            $table->string('status')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_planning_detail');
    }
};
