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
        Schema::table('company_policy_detail', function (Blueprint $table) {
            $table->longText('strategic_goal')->nullable()->change();     // judul / nama strategic goal
            $table->longText('description')->nullable()->change(); // deskripsi strategic goal
            $table->longText('strategic_goal_id');
            $table->longText('description_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_policy_detail', function (Blueprint $table) {
            //
        });
    }
};
