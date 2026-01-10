<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel transaksional: Detail approver per request.
     * Snapshot dari daftar approver (uppline + master) beserta statusnya.
     */
    public function up(): void
    {
        Schema::create('approval_request_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id')
                ->comment('FK ke approval_requests');
            $table->enum('phase', ['uppline', 'master_flow'])
                ->comment('Phase: uppline atau master_flow');
            $table->integer('level_sequence')
                ->comment('Urutan level dalam phase');
            $table->unsignedBigInteger('employment_id')
                ->comment('FK ke employment (approver)');
            $table->string('employment_name', 100)
                ->comment('Snapshot nama approver');
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('request_id')
                ->references('id')
                ->on('approval_requests')
                ->onDelete('cascade');

            $table->foreign('employment_id')
                ->references('id')
                ->on('employment')
                ->onDelete('restrict');

            // Index untuk query lookup
            $table->index(['request_id', 'phase', 'level_sequence']);
            $table->index(['employment_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_request_details');
    }
};
