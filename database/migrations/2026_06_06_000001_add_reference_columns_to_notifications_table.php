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
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('category_id');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            $table->index(['reference_type', 'reference_id'], 'notifications_reference_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_reference_index');
            $table->dropColumn(['reference_type', 'reference_id']);
        });
    }
};
