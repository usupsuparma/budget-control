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
            $table->string('status')->comment('0:Submission|1:Progress|2:Approved|3:Paid|4:Completed|5:Rejected|-1:Cancelled')->default('0')->change();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('status')->comment('0:Submission|1:Progress|2:Paid|3:Complete|4:Reject|-1:Cancelled')->change();
        });
    }
};
