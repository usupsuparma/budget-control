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
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('status_approval', ['pending', 'in_progress', 'approved', 'rejected', 'cancelled'])
                  ->default('pending')
                  ->after('status')
                  ->comment('Approval status of the transaction using dynamic approval system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('status_approval');
        });
    }
};
