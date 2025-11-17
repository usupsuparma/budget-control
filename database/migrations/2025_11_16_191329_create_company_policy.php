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
        Schema::create('company_policy', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');                 // contoh: 2025
            $table->string('nama_dokumen');        // misal: "Rencana Strategis 2025"
            $table->string('file_path');           // path file upload (storage)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_policy');
    }
};
