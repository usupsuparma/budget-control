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
        Schema::create('transaction_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('approver_id');
            $table->string('approver_name');
            $table->tinyInteger('approval_level'); // 1 = Parent, 2 =
            $table->unsignedBigInteger('threshold_id')->nullable()
                ->comment('FK ke approval_thresholds');
                
            $table->boolean('is_required')->default(true)
                ->comment('Apakah approval ini wajib');
            $table->tinyInteger('status')->comment('0 = Pending, 1 = Approved, 2 = Rejected'); // 0 = Pending, 1 = Approved, 2 = Rejected
            $table->dateTime('approved_at')->nullable();
            $table->text('comments')->nullable();
            
            $table->tinyInteger('sequence_order')->nullable()
                ->comment('Urutan approval (1, 2, 3, dst)');
            $table->timestamp('notified_at')->nullable()
                ->comment('Kapan notifikasi dikirim');
            $table->tinyInteger('reminder_count')->default(0)
                ->comment('Jumlah reminder yang sudah dikirim');
            $table->timestamp('reminder_last_sent')->nullable()
                ->comment('Kapan reminder terakhir dikirim');
            $table->string('approval_method', 50)->nullable()
                ->comment('Method approval: web, email, mobile');
            $table->string('ip_address', 45)->nullable()
                ->comment('IP address saat approve');
                        
            // Indexes
            $table->index(['transaction_id', 'status'], 'idx_transaction_status');
            $table->index(['transaction_id', 'sequence_order'], 'idx_transaction_sequence');
            $table->index('threshold_id');
            $table->index('notified_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_approvals');
    }
};
