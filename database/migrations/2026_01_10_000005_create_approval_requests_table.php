<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel transaksional: Header request approval.
     * Dibuat setiap kali user submit request yang memerlukan approval.
     * Menyimpan snapshot konfigurasi agar immutable.
     */
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id')
                ->comment('FK ke approval_modules');
            $table->unsignedBigInteger('reference_id')
                ->comment('ID dari tabel asli (invoice.id, transaction.id)');
            $table->string('reference_number', 50)
                ->comment('Nomor dokumen (INV-001, TRX-001)');
            $table->unsignedBigInteger('template_id')
                ->comment('FK ke approval_flow_templates');
            $table->json('template_snapshot')
                ->comment('Backup konfigurasi approval saat submit');
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])
                ->default('draft');
            $table->enum('current_phase', ['uppline', 'master_flow'])
                ->default('uppline')
                ->comment('Phase saat ini: uppline atau master_flow');
            $table->integer('current_level')
                ->default(1)
                ->comment('Level saat ini dalam phase');
            $table->integer('total_levels')
                ->comment('Total semua level (uppline + master)');
            $table->unsignedBigInteger('requester_id')
                ->comment('FK ke employment (yang submit request)');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('module_id')
                ->references('id')
                ->on('approval_modules')
                ->onDelete('restrict');

            $table->foreign('template_id')
                ->references('id')
                ->on('approval_flow_templates')
                ->onDelete('restrict');

            $table->foreign('requester_id')
                ->references('id')
                ->on('employment')
                ->onDelete('restrict');

            // Index untuk query lookup
            $table->index(['module_id', 'reference_id']);
            $table->index(['status', 'current_phase']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
