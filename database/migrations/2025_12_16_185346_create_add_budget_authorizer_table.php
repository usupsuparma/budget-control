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
        Schema::create('add_budget_authorizer', function (Blueprint $table) {
            $table->id();
            $table->integer('level_number')->nullable();
            $table->string('authorizer_name', 100)->nullable();
            $table->integer('employee_id')->nullable();
            $table->integer('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_budget_authorizer');
    }
};
