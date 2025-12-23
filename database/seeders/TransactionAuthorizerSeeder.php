<?php

namespace Database\Seeders;

use App\Models\TransactionAuthorizer;
use Illuminate\Database\Seeder;

class TransactionAuthorizerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authorizers = [
            // Level 1 - Supervisor
            [
                'level_number' => 1,
                'authorizer_name' => 'Supervisor Finance',
                'authority' => 'Finance',
                'employee_id' => 1, // Sesuaikan dengan employee_id yang ada
                'status' => 1,
                'position_code' => 'SPV-FIN',
                'approval_level' => 1,
                'max_approval_amount' => 10000000,
                'can_override' => false,
                'priority_order' => 1,
            ],
            // Level 2 - Manager
            [
                'level_number' => 2,
                'authorizer_name' => 'Manager Finance',
                'authority' => 'Finance',
                'employee_id' => 2, // Sesuaikan dengan employee_id yang ada
                'status' => 1,
                'position_code' => 'MGR-FIN',
                'approval_level' => 2,
                'max_approval_amount' => 50000000,
                'can_override' => false,
                'priority_order' => 1,
            ],
            // Level 3 - Direktur
            [
                'level_number' => 3,
                'authorizer_name' => 'Direktur Finance',
                'authority' => 'Finance',
                'employee_id' => 3, // Sesuaikan dengan employee_id yang ada
                'status' => 1,
                'position_code' => 'DIR-FIN',
                'approval_level' => 3,
                'max_approval_amount' => 500000000,
                'can_override' => true,
                'priority_order' => 1,
            ],
            // Level 4 - CEO
            [
                'level_number' => 4,
                'authorizer_name' => 'CEO',
                'authority' => 'Executive',
                'employee_id' => 4, // Sesuaikan dengan employee_id yang ada
                'status' => 1,
                'position_code' => 'CEO',
                'approval_level' => 4,
                'max_approval_amount' => 1000000000,
                'can_override' => true,
                'priority_order' => 1,
            ],
            // Level 5 - Board of Directors
            [
                'level_number' => 5,
                'authorizer_name' => 'Board of Directors',
                'authority' => 'Board',
                'employee_id' => 5, // Sesuaikan dengan employee_id yang ada
                'status' => 1,
                'position_code' => 'BOD',
                'approval_level' => 5,
                'max_approval_amount' => null, // Unlimited
                'can_override' => true,
                'priority_order' => 1,
            ],
        ];

        foreach ($authorizers as $authorizer) {
            TransactionAuthorizer::updateOrCreate(
                [
                    'approval_level' => $authorizer['approval_level'],
                    'position_code' => $authorizer['position_code'],
                ],
                $authorizer
            );
        }

        $this->command->info('Transaction Authorizers seeded successfully!');
    }
}
