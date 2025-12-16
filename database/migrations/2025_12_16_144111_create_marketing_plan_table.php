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
        Schema::create('marketing_plan', function (Blueprint $table) {
            $table->id();

            $table->year('year')->nullable();
            $table->string('customer')->nullable();
            $table->string('customer_detail')->nullable();
            $table->string('segment')->nullable();
            $table->string('brand')->nullable();
            $table->string('conc')->nullable();
            $table->string('packing1')->nullable();
            $table->string('packing2')->nullable();
            $table->string('area1')->nullable();
            $table->string('area2')->nullable();
            $table->decimal('product_kg', 18, 2)->nullable();
            $table->decimal('price', 18, 2)->nullable();
            $table->decimal('increase_decrease_price', 18, 2)->nullable();

            // AUTO-GENERATE monthly columns 1..12
            for ($i = 1; $i <= 12; $i++) {

                $table->decimal($i . '_diff', 18, 2)->nullable();
                $table->decimal($i . '_sales_budget', 18, 2)->nullable();
                $table->decimal($i . '_vc', 18, 2)->nullable();
                $table->decimal($i . '_fc', 18, 2)->nullable();
                $table->decimal($i . '_other_cost', 18, 2)->nullable();
                $table->decimal($i . '_trans', 18, 2)->nullable();
                $table->decimal($i . '_pack', 18, 2)->nullable();
                $table->decimal($i . '_total_cost', 18, 2)->nullable();
                $table->decimal($i . '_sales_price', 18, 2)->nullable();
                $table->decimal($i . '_margin', 18, 2)->nullable();
                $table->decimal($i . '_percent', 18, 2)->nullable()->comment('percentage');
                $table->decimal($i . '_sales_qty', 18, 2)->nullable();
                $table->decimal($i . '_sales_amount', 18, 2)->nullable();
                $table->decimal($i . '_margin_amount', 18, 2)->nullable();
                $table->decimal($i . '_full_cost', 18, 2)->nullable();
                $table->decimal($i . '_transport_amount', 18, 2)->nullable();
                $table->decimal($i . '_packing_amount', 18, 2)->nullable();
            }

            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_plan');
    }
};
