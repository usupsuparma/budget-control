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
        Schema::create('employment', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id')->nullable();
            $table->string('organization_id', 100)->nullable();
            $table->string('organization_name', 100)->nullable();
            $table->string('job_level_id', 100)->nullable();
            $table->string('job_level_name', 100)->nullable();
            $table->string('job_position_id', 100)->nullable();
            $table->string('job_position_name', 100)->nullable();
            $table->string('employment_status', 100)->nullable();
            $table->integer('role_id')->nullable();
            $table->string('role_name', 100)->nullable();
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
        Schema::dropIfExists('employment');
    }
};
