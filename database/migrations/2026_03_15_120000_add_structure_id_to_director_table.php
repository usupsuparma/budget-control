<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('director', function (Blueprint $table) {
            // Add nullable structure_id after code
            $table->unsignedBigInteger('structure_id')->nullable()->after('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('director', function (Blueprint $table) {
            if (Schema::hasColumn('director', 'structure_id')) {
                $table->dropColumn('structure_id');
            }
        });
    }
};
