<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset semua employee_id di employment ke null
        // DB::table('employment')->update(['employee_id' => null]);

        // Tambah kolom join_date untuk tracking tanggal masuk
        Schema::table('employment', function (Blueprint $table) {
            $table->date('join_date')->nullable()->after('role_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employment', function (Blueprint $table) {
            $table->dropColumn('join_date');
        });
    }
};
