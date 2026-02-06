<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Snapshot table for LPJ approval chain.
     * Created when LPJ is submitted, immutable after creation.
     */
    public function up(): void
    {
        Schema::create('lpj_approval_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lpj_submission_id');
            $table->unsignedBigInteger('employment_id')->comment('Employment ID of approver');
            $table->integer('level_sequence')->comment('Order of approval');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable()->comment('Approver notes/comments');
            $table->timestamp('actioned_at')->nullable();
            $table->timestamps();
            
            $table->foreign('lpj_submission_id')
                ->references('id')
                ->on('transaction_lpj_submissions')
                ->onDelete('cascade');
                
            $table->foreign('employment_id')
                ->references('id')
                ->on('employment')
                ->onDelete('cascade');

            // Unique constraint
            $table->unique(['lpj_submission_id', 'level_sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpj_approval_details');
    }
};
