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
        Schema::create('kpi_workplans', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship
            $table->enum('kpi_type', ['department', 'section']);
            $table->unsignedBigInteger('kpi_id');
            
            $table->integer('year');
            $table->text('activity');
            $table->unsignedInteger('duration_days')->nullable();
            
            $table->date('schedule_start')->nullable();
            $table->date('schedule_end')->nullable();
            
            // Planning months
            $table->boolean('plan_jan')->default(false);
            $table->boolean('plan_feb')->default(false);
            $table->boolean('plan_mar')->default(false);
            $table->boolean('plan_apr')->default(false);
            $table->boolean('plan_may')->default(false);
            $table->boolean('plan_jun')->default(false);
            $table->boolean('plan_jul')->default(false);
            $table->boolean('plan_aug')->default(false);
            $table->boolean('plan_sep')->default(false);
            $table->boolean('plan_oct')->default(false);
            $table->boolean('plan_nov')->default(false);
            $table->boolean('plan_dec')->default(false);
            
            $table->decimal('budget', 15, 2)->nullable();
            
            // Realization months
            $table->boolean('real_jan')->default(false);
            $table->boolean('real_feb')->default(false);
            $table->boolean('real_mar')->default(false);
            $table->boolean('real_apr')->default(false);
            $table->boolean('real_may')->default(false);
            $table->boolean('real_jun')->default(false);
            $table->boolean('real_jul')->default(false);
            $table->boolean('real_aug')->default(false);
            $table->boolean('real_sep')->default(false);
            $table->boolean('real_oct')->default(false);
            $table->boolean('real_nov')->default(false);
            $table->boolean('real_dec')->default(false);
            
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])
                  ->default('draft');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['kpi_type', 'kpi_id']);
            $table->index('year');
            $table->index('status');
            
            // Foreign key untuk approved_by
            $table->foreign('approved_by')
                  ->references('id')
                  ->on('employee')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_workplans');
    }
};
