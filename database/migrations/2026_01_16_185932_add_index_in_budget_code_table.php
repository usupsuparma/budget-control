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
        Schema::table('budget_code', function (Blueprint $table) {
            // 1. PRIORITAS TINGGI: Pencarian Kode (Exact Match)
            // Ini akan membuat pencarian: WHERE stock_code = '...' menjadi kilat.
            $table->index('stock_code'); // Pakai unique jika data pasti unik
            $table->index('goods_code');
            $table->index('inchargeCode');

            // 2. PRIORITAS MENENGAH: Pencarian Teks
            // Membantu pencarian: WHERE name LIKE 'Laptop%' 
            // Dan sorting: ORDER BY name ASC
            $table->index('name');
            $table->index('remarks');
            
            // CATATAN:
            // Jika Anda sering membuat fitur "Global Search" (satu kotak search mencakup semua kolom),
            // index terpisah di atas sudah cukup karena MySQL modern cukup pintar melakukan "Index Merge".
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_code', function (Blueprint $table) {
            $table->dropIndex(['stock_code']);
            $table->dropIndex(['goods_code']);
            $table->dropIndex(['inchargeCode']);
            $table->dropIndex(['name']);
            $table->dropIndex(['remarks']);
        });
    }
};
