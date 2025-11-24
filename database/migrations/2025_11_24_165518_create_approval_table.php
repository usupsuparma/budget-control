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
        Schema::create('approval', function (Blueprint $table) {
            $table->id();
            $table->string('page_name', 100)->nullable();
            $table->integer('approval_layer')->nullable();
            $table->string('layer_1_name', 100)->nullable();
            $table->string('layer_1_level', 100)->nullable();
            $table->integer('layer_1_id')->nullable();
            $table->string('layer_2_name', 100)->nullable();
            $table->string('layer_2_level', 100)->nullable();
            $table->integer('layer_2_id')->nullable();
            $table->string('layer_3_name', 100)->nullable();
            $table->string('layer_3_level', 100)->nullable();
            $table->integer('layer_3_id')->nullable();
            $table->string('layer_4_name', 100)->nullable();
            $table->string('layer_4_level', 100)->nullable();
            $table->integer('layer_4_id')->nullable();
            $table->string('layer_5_name', 100)->nullable();
            $table->string('layer_5_level', 100)->nullable();
            $table->integer('layer_5_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval');
    }
};
