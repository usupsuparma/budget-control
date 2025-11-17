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
        Schema::create('company_policy_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_policy_id')
                  ->constrained('company_policy')
                  ->cascadeOnDelete();            // jika company_policy dihapus, detail ikut terhapus

            $table->string('strategic_goal');     // judul / nama strategic goal
            $table->text('description')->nullable(); // deskripsi strategic goal
            $table->text('target')->nullable(); // target strategic goal
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_policy_detail');
    }
};
