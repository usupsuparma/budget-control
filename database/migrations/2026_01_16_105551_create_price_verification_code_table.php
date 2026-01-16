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
        Schema::create('price_verification_code', function (Blueprint $table) {
            $table->id();
            $table->integer('price_verification_id')->nullable();
            $table->string('remarks', 100)->nullable();
            $table->string('inchargecode', 10)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_verification_code');
    }
};
