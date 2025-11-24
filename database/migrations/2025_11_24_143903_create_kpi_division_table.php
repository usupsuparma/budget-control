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
        Schema::create('kpi_division', function (Blueprint $table) {
            $table->id();

            /**
             * RELASI
             * - company_policy_id → ke tabel company_policy (kolom id)
             * - division_id       → ke tabel division (kolom id)
             */
            $table->foreignId('company_policy_detail_id')
                  ->constrained('company_policy_detail')
                  ->cascadeOnDelete();

            $table->foreignId('division_id')
                  ->constrained('division')
                  ->cascadeOnDelete();

            // Tahun KPI, misal 2026
            $table->year('year');

            // Data KPI
            $table->text('division_goals')->nullable();         // Division Goals 2026 / KPI Divisi 2026

            // Target
            $table->string('target_division')->nullable();      // kolom DIVISION pada TARGET
            $table->unsignedInteger('duration_days')->nullable(); // (DAYS)

            // Schedule
            $table->date('schedule_start')->nullable();         // START
            $table->date('schedule_end')->nullable();           // END

            // CHARTS 2026: JAN – DEC (boolean 0/1)
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
            $table->string('revenue_cost')->nullable();         // Revenue / Cost
            $table->string('pic')->nullable();                  // PIC
            $table->text('description')->nullable();            // Description

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_division');
    }
};
