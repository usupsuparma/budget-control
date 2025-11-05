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
        Schema::create('email_content', function (Blueprint $table) {
            $table->id();
            $table->text('subject')->nullable();
            $table->text('text')->nullable();
            $table->string('sender')->nullable();
            $table->text('link')->nullable();
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
        Schema::dropIfExists('email_content');
    }
};
