<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Master table to define which employees can approve LPJ submissions.
     * Simple approval flow with sequence order.
     */
    public function up(): void
    {
        Schema::create('lpj_approval_masters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employment_id')->comment('Employment ID of approver');
            $table->integer('approval_sequence')->comment('Order of approval (1 = first, 2 = second, etc.)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('employment_id')
                ->references('id')
                ->on('employment')
                ->onDelete('cascade');

            // Unique constraint to prevent duplicate sequence
            $table->unique(['employment_id', 'approval_sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpj_approval_masters');
    }
};
