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
        Schema::create('email_notification', function (Blueprint $table) {
            $table->id();
            $table->string('notification_name', 100)->nullable();
            $table->string('employee_id', 100)->nullable();
            $table->integer('email_content_id')->nullable();
            $table->string('route', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_notification');
    }
};
