<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_section', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kpi_department_id')
                  ->constrained('kpi_department')
                  ->cascadeOnDelete();

            $table->foreignId('section_id')
                  ->constrained('section')
                  ->cascadeOnDelete();

            $table->integer('year');             // ikut dari parent

            // Department & Section Goals
            $table->text('section_goals');                  // Section Goals 2026 / KPI Section

            // Activities & Target
            $table->text('activities')->nullable();         // ACTIVITIES
            $table->string('target_section')->nullable();   // TARGET SECTION
            $table->unsignedInteger('duration_days')->nullable(); // DURATION (DAYS)

            // Schedule
            $table->date('schedule_start')->nullable();
            $table->date('schedule_end')->nullable();

            // CHARTS 2026 (bulan)
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

            // Remarks
            $table->string('revenue_cost')->nullable();
            $table->string('unit_id')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_section');
    }
};
