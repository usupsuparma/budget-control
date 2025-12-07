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
        Schema::create('workplan_budget_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kpi_workplan_id');
            $table->unsignedBigInteger('budget_category_id');
            
            // Detail Budget Fields
            $table->text('description');
            $table->string('stock_code', 50)->nullable();
            $table->string('budget_code', 50)->nullable();
            $table->string('product_line', 100)->nullable();
            $table->string('cost_center', 50)->nullable();
            $table->string('beg_balance', 100)->nullable(); // string sesuai permintaan
            $table->string('cons_rate', 100)->nullable(); // string sesuai permintaan
            $table->string('unit', 50)->nullable();
            $table->decimal('total', 15, 2)->default(0);
            
            // Monthly Activities Quantity (0 - 1000)
            $table->unsignedSmallInteger('activity_jan')->default(0);
            $table->unsignedSmallInteger('activity_feb')->default(0);
            $table->unsignedSmallInteger('activity_mar')->default(0);
            $table->unsignedSmallInteger('activity_apr')->default(0);
            $table->unsignedSmallInteger('activity_may')->default(0);
            $table->unsignedSmallInteger('activity_jun')->default(0);
            $table->unsignedSmallInteger('activity_jul')->default(0);
            $table->unsignedSmallInteger('activity_aug')->default(0);
            $table->unsignedSmallInteger('activity_sep')->default(0);
            $table->unsignedSmallInteger('activity_oct')->default(0);
            $table->unsignedSmallInteger('activity_nov')->default(0);
            $table->unsignedSmallInteger('activity_dec')->default(0);
            
            // Status & Approval
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])
                  ->default('draft');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Additional Fields
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign Keys
            $table->foreign('kpi_workplan_id')
                  ->references('id')
                  ->on('kpi_workplans')
                  ->onDelete('cascade');
            
            $table->foreign('budget_category_id')
                  ->references('id')
                  ->on('budget_categories')
                  ->onDelete('restrict');
            
            $table->foreign('budget_code')
                  ->references('code')
                  ->on('budget_codes')
                  ->onDelete('set null');
            
            $table->foreign('approved_by')
                  ->references('id')
                  ->on('employee')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('kpi_workplan_id');
            $table->index('budget_category_id');
            $table->index('status');
            $table->index('budget_code');
            $table->index(['kpi_workplan_id', 'status']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workplan_budget_items');
    }
};
