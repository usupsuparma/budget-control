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
     * Step 1: Update employment.employee_id values from NIP (varchar) to employee.id (integer)
     * Step 2: Change column type from varchar to unsignedBigInteger
     */
    public function up(): void
    {
        // Step 1: Update employment.employee_id from NIP to employee.id
        // The current employee_id contains NIP which matches employee.employee_id (akan jadi employee_code)
        DB::statement("
            UPDATE employment e
            INNER JOIN employee emp ON e.employee_id = emp.employee_id
            SET e.employee_id = emp.id
            WHERE e.employee_id IS NOT NULL
        ");

        // Step 2: Change column type
        Schema::table('employment', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Revert column type
        Schema::table('employment', function (Blueprint $table) {
            $table->string('employee_id', 50)->nullable()->change();
        });

        // Step 2: Revert employment.employee_id from id back to NIP (employee_code)
        // At this point, employee.employee_id has been renamed back to employee_id from employee_code
        DB::statement("
            UPDATE employment e
            INNER JOIN employee emp ON e.employee_id = emp.id
            SET e.employee_id = emp.employee_id
            WHERE e.employee_id IS NOT NULL
        ");
    }
};
