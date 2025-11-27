<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_department', function (Blueprint $table) {
            $table->id();

            // RELASI: ke KPI Division
            $table->foreignId('kpi_division_id')
                  ->constrained('kpi_division')
                  ->cascadeOnDelete();

            // RELASI: ke Department
            $table->foreignId('department_id')
                  ->constrained('department')
                  ->cascadeOnDelete();

            // Tahun ikut parent (kpi_division), tapi tetap disimpan
            $table->integer('year');

            // nomor urut baris (opsional)
            $table->unsignedInteger('no')->nullable();

            // GOALS & ACTIVITIES
            $table->text('division_goals')->nullable();        // Division Goals 2026 / KPI Divisi 2026
            $table->text('department_goals');                  // Department Goals 2026 / KPI Departemen 2026
            $table->text('department_activities')->nullable(); // Department Activities

            // TARGET & DURASI
            $table->string('target_department')->nullable();   // TARGET DEPARTMENT
            $table->unsignedInteger('duration_days')->nullable(); // DURATION (DAYS)

            // SCHEDULE
            $table->date('schedule_start')->nullable();
            $table->date('schedule_end')->nullable();

            // CHARTS (JAN–DEC)
            $table->boolean('jan')->default(false);
            $table->boolean('feb')->default(false);
            $table->boolean('mar')->default(false);
            $table->boolean('apr')->default(false);
            $table->boolean('may')->default(false);
            $table->boolean('jun')->default(false);
            $table->boolean('jul')->default(false);
            $table->boolean('aug')->default(false);
            $table->boolean('sep')->default(false);
            $table->boolean('oct')->default(false);
            $table->boolean('nov')->default(false);
            $table->boolean('dec')->default(false);

            // REMARKS
            $table->string('revenue_cost')->nullable();
            $table->string('pic')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_department');
    }
};
