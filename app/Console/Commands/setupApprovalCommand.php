<?php

namespace App\Console\Commands;

use App\Models\ApprovalFlowTemplate;
use App\Models\ApprovalModule;
use Illuminate\Console\Command;

class setupApprovalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-approval';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan setup awal untuk modul Approval, termasuk seeding data awal.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
       
        $this->info('Seeding Approval Modules...');
         $modules = [
            
            [
                'module_name' => 'Budget',
                'table_name' => 'workplan_budget_items',
                'condition_field' => 'total',
                'is_active' => true,
            ],
            [
                'module_name' => 'Transactions',
                'table_name' => 'transactions',
                'condition_field' => 'estimated_amount',
                'is_active' => true,
            ],
            [
                'module_name' => 'Workplan',
                'table_name' => 'kpi_workplans',
                'condition_field' => 'budget',
                'is_active' => true,
            ],
        ];
        $count = 0;
        foreach ($modules as $module) {
            ApprovalModule::updateOrCreate(
                [
                    'table_name' => $module['table_name'],
                ],
                $module
            );
            $count++;
        }
        if ($count === 0) {
            $this->info('No new Approval Modules were added.');
            return;
        }

        ApprovalFlowTemplate::updateOrCreate(
            [
                'template_name' => 'Approval Budget Standard',
                'module_id' => ApprovalModule::where('table_name', 'workplan_budget_items')->first()->id,
            ],
            [
                'template_name' => 'Approval Budget Standard',
                'use_uppline_chain' => true,
                'use_threshold' => true,
                'condition_field'=> ApprovalModule::where('table_name', 'workplan_budget_items')->first()->condition_field,
                'module_id' => ApprovalModule::where('table_name', 'workplan_budget_items')->first()->id,
                'description' => 'Default approval flow template for various modules.',
                'is_active' => true,
            ]
        );

        ApprovalFlowTemplate::updateOrCreate(
            [
                'template_name' => 'Approval Transaction Standard',
                'module_id' => ApprovalModule::where('table_name', 'transactions')->first()->id,
            ],
            [
                'template_name' => 'Approval Transaction Standard',
                'use_uppline_chain' => true,
                'use_threshold' => true,
                'condition_field'=> ApprovalModule::where('table_name', 'transactions')->first()->condition_field,
                'module_id' => ApprovalModule::where('table_name', 'transactions')->first()->id,
                'description' => 'Default approval flow template for various modules.',
                'is_active' => true,
            ]
        );

        $this->info('Approval Modules seeded successfully!');
        } catch (\Throwable $th) {
            $this->error('Error seeding Approval Modules: ' . $th->getMessage());
        }
    }
}
