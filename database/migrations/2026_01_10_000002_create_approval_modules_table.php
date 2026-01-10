<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel master: Daftar modul yang menggunakan sistem approval.
     * Contoh: transactions, bookings, invoices
     */
    public function up(): void
    {
        Schema::create('approval_modules', function (Blueprint $table) {
            $table->id();
            $table->string('module_name', 50)
                ->comment("Nama modul: 'transactions', 'invoices', 'bookings'");
            $table->string('table_name', 50)
                ->comment('Nama tabel asli dari modul');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_modules');
    }
};
