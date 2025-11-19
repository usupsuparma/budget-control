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
        Schema::create('job_position', function (Blueprint $table) {
            $table->id();
            $table->string('job_position_name', 100)->nullable();
            $table->integer('job_level_id')->nullable();
            $table->string('job_level_name', 100)->nullable();
            $table->integer('structure_id')->nullable();
            $table->integer('structure_name')->nullable();
            $table->string('status', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_position');
    }
};
