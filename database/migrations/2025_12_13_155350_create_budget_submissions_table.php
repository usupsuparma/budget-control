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
        Schema::create('budget_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('division_id');
            $table->unsignedInteger('work_plan_id');
            $table->string('division_name');
            $table->date('submission_date');
            $table->enum('type', ['add', 'relocation']);
            $table->unsignedInteger('budget_account_id');
            $table->decimal('estimation_amount', 15, 2);
            $table->string('description')->nullable();
            $table->unsignedSmallInteger('status')->default(0); // 0: pending, 1: approved, 2: rejected
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_submissions');
    }
};
