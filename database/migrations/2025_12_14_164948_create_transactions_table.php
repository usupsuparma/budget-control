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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->unsignedInteger('user_id');
            $table->string('user_name');
            $table->unsignedInteger('unit_id');
            $table->string('unit_name');
            $table->string('purpose')->nullable();
            $table->unsignedInteger('estimated_amount')->default(0);
            $table->unsignedInteger('actual_amount')->default(0);
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
        Schema::dropIfExists('transactions');
    }
};
