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
        Schema::create('transaction_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->comment('FK ke transactions');
            $table->unsignedBigInteger('approval_id')->nullable()->comment('FK ke transaction_approvals');
            $table->string('action', 50)->comment('create, approve, reject, delegate, escalate');
            $table->unsignedBigInteger('actor_id')->comment('User yang melakukan action');
            $table->string('actor_name')->comment('Nama user');
            $table->tinyInteger('from_status')->nullable()->comment('Status sebelumnya');
            $table->tinyInteger('to_status')->nullable()->comment('Status baru');
            $table->text('notes')->nullable()->comment('Catatan atau komentar');
            $table->json('metadata')->nullable()->comment('Data tambahan dalam JSON');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
                        
            // Indexes
            $table->index('transaction_id');
            $table->index('approval_id');
            $table->index('actor_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_approval_logs');
    }
};
