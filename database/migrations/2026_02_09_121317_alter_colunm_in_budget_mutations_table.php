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
        Schema::table('budget_mutations', function (Blueprint $table) {
            //transaction_id to nullable
            $table->unsignedBigInteger('transaction_id')->nullable()->change(); 
            // transaction_detail_id to nullable
            $table->unsignedBigInteger('transaction_detail_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_mutations', function (Blueprint $table) {
            //transaction_id to not nullable
            $table->unsignedBigInteger('transaction_id')->nullable(false)->change();
        });
    }
};
