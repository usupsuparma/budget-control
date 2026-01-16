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
        Schema::create('approval_flow_uppline_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->unsignedBigInteger('division_id')->nullable();
            $table->integer('step_sequence');
            $table->string('job_level_name', 100);
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            // $table->foreign('template_id')
            //     ->references('id')
            //     ->on('approval_flow_templates')
            //     ->onDelete('cascade');

            // $table->foreign('division_id')
            //     ->references('id')
            //     ->on('division')
            //     ->onDelete('cascade');

            // Indexes for query performance
            $table->index(['template_id', 'division_id', 'step_sequence'], 'uppline_config_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_flow_uppline_configs');
    }
};
