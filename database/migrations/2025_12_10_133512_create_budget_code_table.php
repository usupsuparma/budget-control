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
        Schema::create('budget_code', function (Blueprint $table) {
            $table->id();
            $table->string('stock_code')->nullable();
            $table->string('name')->nullable();
            $table->integer('active_flag')->nullable();
            $table->integer('user_no')->nullable();
            $table->text('memo')->nullable();
            $table->date('delivdate')->nullable();
            $table->string('inchargeCode')->nullable();
            $table->integer('remarks_id')->nullable();
            $table->string('remarks')->nullable();
            $table->string('goods_code')->nullable();
            $table->integer('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_code');
    }
};
