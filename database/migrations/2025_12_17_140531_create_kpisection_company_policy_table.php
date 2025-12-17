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
        Schema::create('kpisection_company_policy', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');                 // contoh: 2025
            $table->longText('header')->nullable();
            $table->longText('contents_en')->nullable();
            $table->longText('contents_id')->nullable();
            $table->longText('prologue_en')->nullable();
            $table->longText('prologue_id')->nullable();
            $table->longText('closing_en')->nullable();
            $table->longText('closing_id')->nullable();
            $table->longText('signature')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpisection_company_policy');
    }
};
