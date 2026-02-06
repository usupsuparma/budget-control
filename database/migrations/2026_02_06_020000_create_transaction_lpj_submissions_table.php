<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table to store LPJ (Laporan Pertanggungjawaban) submissions.
     * Each transaction can have one LPJ submission after status becomes PAID.
     */
    public function up(): void
    {
        Schema::create('transaction_lpj_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->date('submission_date')->comment('Tanggal lapor LPJ');
            $table->date('realization_date')->comment('Tanggal aktual belanja');
            $table->string('proof_of_payment')->nullable()->comment('Path file upload bukti');
            
            // Approval LPJ Logic
            $table->enum('status_approval', ['pending', 'in_progress', 'approved', 'rejected'])
                ->default('pending');
            $table->integer('current_approval_level')->default(0);
            $table->integer('total_approval_levels')->default(0);
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable()->comment('Employment ID of final approver');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')
                ->onDelete('cascade');
                
            $table->foreign('approved_by')
                ->references('id')
                ->on('employment')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_lpj_submissions');
    }
};
