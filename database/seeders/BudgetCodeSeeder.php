<?php

namespace Database\Seeders;

use App\Models\BudgetCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BudgetCodeSeeder extends Seeder
{
    public function run(): void
    {
        $budgetCodes = [
            [
                'code' => 'BC-001',
                'name' => 'Raw Material',
                'category' => 'Material',
                'description' => 'Budget code for raw materials',
            ],
            [
                'code' => 'BC-002',
                'name' => 'Equipment',
                'category' => 'Investment',
                'description' => 'Budget code for equipment investment',
            ],
            [
                'code' => 'BC-003',
                'name' => 'Consulting Services',
                'category' => 'Services',
                'description' => 'Budget code for consulting services',
            ],
            [
                'code' => 'BC-004',
                'name' => 'Maintenance',
                'category' => 'Services',
                'description' => 'Budget code for maintenance services',
            ],
            [
                'code' => 'BC-005',
                'name' => 'Spare Parts',
                'category' => 'Inventory',
                'description' => 'Budget code for spare parts inventory',
            ],
        ];

        foreach ($budgetCodes as $code) {
            BudgetCode::create($code);
        }
    }
}
