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
            $table->string('status')->comment('0:Submission|1:Approved Parent|2:Approved Finance|3:Approved Division|4:Approved Finance Director|5:Approved President Director|6:Rejected|7:Paid|8:Complete|-1:Cancelled')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('status')->comment('0:Submission|1:Approved Parent|2:Approved Finance|3:Approved Division|4:Approved Finance Director|5:Approved President Director|6:Rejected|7:Paid|8:Complete|-1:Cancelled')->change();
        });
    }
};
