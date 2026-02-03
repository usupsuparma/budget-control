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
     * Add 'skipped' value to status ENUM in approval_request_details table.
     * This is needed when a transaction is rejected - remaining pending approvers are skipped.
     */
    public function up(): void
    {
        // MySQL specific: Modify ENUM to add 'skipped' value
        DB::statement("ALTER TABLE approval_request_details MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'skipped') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First update any 'skipped' records to 'pending' to avoid data loss
        DB::table('approval_request_details')
            ->where('status', 'skipped')
            ->update(['status' => 'pending']);
            
        // Then modify ENUM back to original values
        DB::statement("ALTER TABLE approval_request_details MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
    }
};
