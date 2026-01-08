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
        Schema::table('employment', function (Blueprint $table) {
            $table->integer('uppline_id')->nullable()->after('job_position_name');
            $table->string('uppline_id_name', 100)->nullable()->after('uppline_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employment', function (Blueprint $table) {
            //
        });
    }
};
