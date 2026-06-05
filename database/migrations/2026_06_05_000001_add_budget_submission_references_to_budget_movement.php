<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('budget_submissions', 'source_budget_account_id')) {
            Schema::table('budget_submissions', function (Blueprint $table) {
                $table->unsignedBigInteger('source_budget_account_id')
                    ->nullable()
                    ->after('budget_account_id');
                $table->index('source_budget_account_id', 'idx_budget_submissions_source_budget_account');
            });
        }

        if (! Schema::hasColumn('budget_mutations', 'budget_submission_id')) {
            Schema::table('budget_mutations', function (Blueprint $table) {
                $table->unsignedBigInteger('budget_submission_id')
                    ->nullable()
                    ->after('transaction_lpj_submission_id');
                $table->index('budget_submission_id', 'idx_budget_mutations_budget_submission');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('budget_mutations', 'budget_submission_id')) {
            Schema::table('budget_mutations', function (Blueprint $table) {
                $table->dropIndex('idx_budget_mutations_budget_submission');
                $table->dropColumn('budget_submission_id');
            });
        }

        if (Schema::hasColumn('budget_submissions', 'source_budget_account_id')) {
            Schema::table('budget_submissions', function (Blueprint $table) {
                $table->dropIndex('idx_budget_submissions_source_budget_account');
                $table->dropColumn('source_budget_account_id');
            });
        }
    }
};
