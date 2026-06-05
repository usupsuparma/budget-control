<?php

namespace App\Console\Commands;

use App\Models\ApprovalFlowTemplate;
use App\Models\ApprovalModule;
use App\Models\BudgetSubmission;
use Illuminate\Console\Command;

class SetupBudgetSubmissionApprovalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-budget-submission-approval';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup approval module dan template default untuk Budget Submission.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $budgetSubmissionTable = (new BudgetSubmission())->getTable();

            $this->info('Seeding Approval Module untuk Budget Submission...');
            $module = ApprovalModule::updateOrCreate(
                ['table_name' => $budgetSubmissionTable],
                [
                    'module_name' => 'Budget Submission',
                    'table_name' => $budgetSubmissionTable,
                    'condition_field' => 'estimation_amount',
                    'is_active' => true,
                ]
            );

            $template = ApprovalFlowTemplate::updateOrCreate(
                [
                    'template_name' => 'Approval Budget Submission Standard',
                    'module_id' => $module->id,
                ],
                [
                    'template_name' => 'Approval Budget Submission Standard',
                    'module_id' => $module->id,
                    'use_uppline_chain' => true,
                    'use_threshold' => true,
                    'condition_field' => $module->condition_field,
                    'priority' => 1,
                    'is_active' => true,
                ]
            );

            $this->info('Berhasil setup approval untuk Budget Submission.');
            $this->line("Module: {$module->module_name} ({$module->table_name})");
            $this->line("Template: {$template->template_name} (ID: {$template->id})");

            return self::SUCCESS;
        } catch (\Throwable $th) {
            $this->error('Error seeding Approval Module untuk Budget Submission: ' . $th->getMessage());

            return self::FAILURE;
        }
    }
}

