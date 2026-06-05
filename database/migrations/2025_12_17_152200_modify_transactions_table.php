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
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('threshold_id')->nullable()->after('status')
                ->comment('FK ke approval_thresholds');
            $table->tinyInteger('current_approval_level')->default(0)->after('threshold_id')
                ->comment('Level approval saat ini (0=belum ada, 1=level 1, dst)');
            $table->tinyInteger('required_approval_levels')->default(0)->after('current_approval_level')
                ->comment('Total level approval yang dibutuhkan');
            $table->timestamp('approval_completed_at')->nullable()->after('required_approval_levels')
                ->comment('Kapan semua approval selesai');
            $table->text('rejection_reason')->nullable()->after('approval_completed_at')
                ->comment('Alasan reject jika ditolak');
            
            
            // Indexes
            $table->index(['status', 'current_approval_level'], 'idx_transactions_status_level');
            $table->index('approval_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('transactions', function (Blueprint $table) {
           
            // drop index
            $table->dropIndex('idx_transactions_status_level');
            $table->dropIndex(['approval_completed_at']);
            
            $table->dropColumn([
                'threshold_id',
                'current_approval_level',
                'required_approval_levels',
                'approval_completed_at',
                'rejection_reason'
            ]);
        });
    }
};
