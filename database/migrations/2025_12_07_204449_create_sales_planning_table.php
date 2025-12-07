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
        Schema::create('sales_planning', function (Blueprint $table) {
            $table->id();
            $table->string('no')->nullable();
            $table->string('actual')->nullable();
            $table->string('sales_segment')->nullable();
            $table->string('brand')->nullable();
            $table->string('conc')->nullable();
            $table->string('packing1')->nullable();
            $table->string('packing2')->nullable();
            $table->string('segment')->nullable();
            $table->string('area1')->nullable();
            $table->string('area2')->nullable();
            $table->string('production_cost')->nullable();
            $table->string('price')->nullable();
            $table->string('increase_decrease_prices')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_planning');
    }
};
