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
        Schema::table('transaction_authorizer', function (Blueprint $table) {
            $table->string('position_code', 50)->nullable()->after('authorizer_name')
                ->comment('Kode jabatan: SF, MF, DF, CEO, BOD');
            $table->tinyInteger('approval_level')->default(1)->after('position_code')
                ->comment('1=Supervisor, 2=Manager, 3=Director, 4=CEO, 5=BOD');
            $table->decimal('max_approval_amount', 15, 2)->nullable()->after('approval_level')
                ->comment('Maksimal nominal yang bisa approve');
            $table->boolean('can_override')->default(false)->after('max_approval_amount')
                ->comment('Bisa override approval level dibawahnya');
            $table->integer('priority_order')->default(0)->after('can_override')
                ->comment('Urutan prioritas jika ada multiple approver di level yang sama');
            
            // Indexes
            $table->index('approval_level', 'idx_approval_level');
            $table->index(['status', 'approval_level'], 'idx_status_level');
            $table->index('position_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_authorizer', function (Blueprint $table) {
            $table->dropIndex('idx_approval_level');
            $table->dropIndex('idx_status_level');
            $table->dropIndex(['position_code']);
            
            $table->dropColumn([
                'position_code',
                'approval_level',
                'max_approval_amount',
                'can_override',
                'priority_order'
            ]);
        });
    }
};
