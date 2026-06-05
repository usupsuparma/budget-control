<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('approval_flow_uppline_configs', function (Blueprint $table) {
            // Make division_id nullable if not already
            if (DB::getDriverName() === 'mysql' && Schema::hasColumn('approval_flow_uppline_configs', 'division_id')) {
                DB::statement('ALTER TABLE approval_flow_uppline_configs MODIFY division_id BIGINT UNSIGNED NULL');
            }
            
            // Add soft deletes if not exists
            if (!Schema::hasColumn('approval_flow_uppline_configs', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Add foreign keys if not exist
        try {
            Schema::table('approval_flow_uppline_configs', function (Blueprint $table) {
                $table->foreign('template_id', 'approval_flow_uppline_configs_template_id_foreign')
                    ->references('id')
                    ->on('approval_flow_templates')
                    ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key already exists, skip
        }

        try {
            Schema::table('approval_flow_uppline_configs', function (Blueprint $table) {
                $table->foreign('division_id', 'approval_flow_uppline_configs_division_id_foreign')
                    ->references('id')
                    ->on('division')
                    ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key already exists, skip
        }

        // Add index if not exists
        try {
            Schema::table('approval_flow_uppline_configs', function (Blueprint $table) {
                $table->index(['template_id', 'division_id', 'step_sequence'], 'uppline_config_lookup');
            });
        } catch (\Exception $e) {
            // Index already exists, skip
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_flow_uppline_configs', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['template_id']);
            $table->dropForeign(['division_id']);
            
            // Drop index
            $table->dropIndex('uppline_config_lookup');
            
            // Drop soft deletes
            $table->dropSoftDeletes();
            
            // Make division_id not nullable again
            $table->unsignedBigInteger('division_id')->nullable(false)->change();
        });
    }
};
