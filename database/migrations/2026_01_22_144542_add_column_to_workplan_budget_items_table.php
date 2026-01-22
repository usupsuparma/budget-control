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
        Schema::table('workplan_budget_items', function (Blueprint $table) {
            //update kolom total from double to unsignedBigInteger
            $table->unsignedBigInteger('total')->change()->default(0);
            $table->enum('verification_status', ['unverified', 'pending', 'verified', 'rejected'])->default('unverified')->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workplan_budget_items', function (Blueprint $table) {
            //
            $table->double('total')->change()->default(0);
            $table->dropColumn('verification_status');
        });
    }
};
