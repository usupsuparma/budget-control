<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel master: Detail approver untuk setiap template.
     * Berisi: siapa approver, threshold nominal, urutan level.
     */
    public function up(): void
    {
        Schema::create('approval_flow_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id')
                ->comment('FK ke approval_flow_templates');
            $table->integer('level_sequence')
                ->comment('Urutan level: 1, 2, 3...');
            $table->unsignedBigInteger('employment_id')
                ->comment('FK ke employment (approver)');
            $table->unsignedBigInteger('threshold_amount')
                ->nullable()
                ->comment('Batas nominal (jika use_threshold=TRUE)');
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->foreign('template_id')
                ->references('id')
                ->on('approval_flow_templates')
                ->onDelete('cascade');

            $table->foreign('employment_id')
                ->references('id')
                ->on('employment')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_flow_details');
    }
};
