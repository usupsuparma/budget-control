<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds a foreign key constraint from employment.employee_id 
     * to employee.id. After this migration:
     * - employment.employee_id references employee.id (integer PK)
     * - employee.employee_code contains the NIP (Nomor Induk Pegawai)
     * 
     * Note: Data migration already done in previous migration (2026_01_27_163127)
     */
    public function up(): void
    {
        // Add foreign key constraint
        Schema::table('employment', function (Blueprint $table) {
            $table->foreign('employee_id')
                ->references('id')
                ->on('employee')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employment', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });
    }
};
