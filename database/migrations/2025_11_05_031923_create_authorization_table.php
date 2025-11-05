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
        Schema::create('authorization', function (Blueprint $table) {
            $table->id();
            $table->string('authorization_name', 100)->nullable();
            $table->integer('authorization_layer')->nullable();
            $table->string('level_1', 100)->nullable();
            $table->string('user_level_1', 100)->nullable();
            $table->string('status_level_1', 100)->nullable();
            $table->string('level_2', 100)->nullable();
            $table->string('user_level_2', 100)->nullable();
            $table->string('status_level_2', 100)->nullable();
            $table->string('level_3', 100)->nullable();
            $table->string('user_level_3', 100)->nullable();
            $table->string('status_level_3', 100)->nullable();
            $table->string('level_4', 100)->nullable();
            $table->string('user_level_4', 100)->nullable();
            $table->string('status_level_4', 100)->nullable();
            $table->string('level_5', 100)->nullable();
            $table->string('user_level_5', 100)->nullable();
            $table->string('status_level_5', 100)->nullable();
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
        Schema::dropIfExists('authorization');
    }
};
