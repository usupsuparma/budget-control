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
        Schema::table('employee', function (Blueprint $table) {
            //drop column job_position_id if exists
            if (Schema::hasColumn('employee', 'job_position_id')) {
                $table->dropColumn('job_position_id');
            }
            $table->date('birth_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee', function (Blueprint $table) {
            //
            if (!Schema::hasColumn('employee', 'job_position_id')) {
                $table->string('job_position_id', 50)->nullable();
            }
            $table->date('birth_date')->nullable(false)->change();
        });
    }
};
