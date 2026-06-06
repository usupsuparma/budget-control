<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('budget_submissions', 'approved_amount')) {
            Schema::table('budget_submissions', function (Blueprint $table) {
                $table->unsignedBigInteger('approved_amount')
                    ->nullable()
                    ->after('estimation_amount');
            });
        }

        if (! Schema::hasColumn('budget_submissions', 'approved_amount_changed_by')) {
            Schema::table('budget_submissions', function (Blueprint $table) {
                $table->unsignedBigInteger('approved_amount_changed_by')
                    ->nullable()
                    ->after('approved_amount');
            });
        }

        if (! Schema::hasColumn('budget_submissions', 'approved_amount_changed_at')) {
            Schema::table('budget_submissions', function (Blueprint $table) {
                $table->timestamp('approved_amount_changed_at')
                    ->nullable()
                    ->after('approved_amount_changed_by');
            });
        }
    }

    public function down(): void
    {
        Schema::table('budget_submissions', function (Blueprint $table) {
            foreach ([
                'approved_amount_changed_at',
                'approved_amount_changed_by',
                'approved_amount',
            ] as $column) {
                if (Schema::hasColumn('budget_submissions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
