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
        Schema::create('production_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->constrained('productions')->cascadeOnDelete();

            $table->string('detail');

            $table->decimal('jan', 18, 2)->default(0);
            $table->decimal('feb', 18, 2)->default(0);
            $table->decimal('mar', 18, 2)->default(0);
            $table->decimal('apr', 18, 2)->default(0);
            $table->decimal('may', 18, 2)->default(0);
            $table->decimal('jun', 18, 2)->default(0);
            $table->decimal('jul', 18, 2)->default(0);
            $table->decimal('aug', 18, 2)->default(0);
            $table->decimal('sep', 18, 2)->default(0);
            $table->decimal('oct', 18, 2)->default(0);
            $table->decimal('nov', 18, 2)->default(0);
            $table->decimal('dec', 18, 2)->default(0);

            $table->decimal('total', 18, 2)->default(0);

            $table->timestamps();

            $table->index(['production_id','detail']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_detail');
    }
};
