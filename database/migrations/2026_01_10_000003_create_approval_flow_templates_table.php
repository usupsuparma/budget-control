<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel master: Template/aturan approval per modul.
     * Mengatur: pakai uppline chain? pakai threshold?
     */
    public function up(): void
    {
        Schema::create('approval_flow_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id')
                ->comment('FK ke approval_modules');
            $table->string('template_name', 100);
            $table->boolean('use_uppline_chain')
                ->default(false)
                ->comment('TRUE = pakai uppline chain sebelum master flow');
            $table->boolean('use_threshold')
                ->default(false)
                ->comment('TRUE = filter approver berdasarkan nominal');
            $table->string('condition_field', 50)
                ->nullable()
                ->comment("Field untuk threshold: 'amount', 'total', dll");
            $table->integer('priority')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('module_id')
                ->references('id')
                ->on('approval_modules')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_flow_templates');
    }
};
