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
        Schema::create('stock_code', function (Blueprint $table) {
            $table->id();
            $table->string('stock_code');
            $table->string('name');
            $table->string('unit')->nullable();
            $table->string('budget_code')->nullable();
            $table->string('active')->nullable();
            $table->string('warehouse')->nullable();
            $table->string('category')->nullable();
            $table->string('product_line')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_code');
    }
};
