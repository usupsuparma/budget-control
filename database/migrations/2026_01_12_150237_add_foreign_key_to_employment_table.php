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
        Schema::table('employment', function (Blueprint $table) {
            // Ubah kolom employee_id menjadi string untuk match dengan employee.employee_id
            $table->string('employee_id', 50)->nullable()->change();
            
            // Tambahkan foreign key constraint
            $table->foreign('employee_id')
                  ->references('employee_id')
                  ->on('employee')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employment', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->integer('employee_id')->nullable()->change();
        });
    }
};
