<?php

namespace Database\Seeders;

use App\Models\TransactionApprovalThreshold;
use Illuminate\Database\Seeder;

class ApprovalThresholdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $thresholds = [
            [
                'min_amount' => 0,
                'max_amount' => 10000000, // 10 juta
                'approval_sequence' => 2,
                'required_levels' => [1, 2], // Supervisor & Manager
                'description' => 'Approval s/d Manager Finance',
                'is_active' => true,
            ],
            [
                'min_amount' => 10000001,
                'max_amount' => 100000000, // 100 juta
                'approval_sequence' => 3,
                'required_levels' => [1, 2, 3], // Supervisor, Manager & Direktur
                'description' => 'Approval s/d Direktur Finance',
                'is_active' => true,
            ],
            [
                'min_amount' => 100000001,
                'max_amount' => 1000000000, // 1 miliar
                'approval_sequence' => 4,
                'required_levels' => [1, 2, 3, 4], // Sampai CEO
                'description' => 'Approval s/d CEO',
                'is_active' => true,
            ],
            [
                'min_amount' => 1000000001,
                'max_amount' => 999999999999, // Unlimited (large number)
                'approval_sequence' => 5,
                'required_levels' => [1, 2, 3, 4, 5], // Sampai Board
                'description' => 'Approval s/d Board of Directors',
                'is_active' => true,
            ],
        ];

        foreach ($thresholds as $threshold) {
            TransactionApprovalThreshold::updateOrCreate(
                [
                    'min_amount' => $threshold['min_amount'],
                    'max_amount' => $threshold['max_amount'],
                ],
                $threshold
            );
        }

        $this->command->info('Approval Thresholds seeded successfully!');
    }
}
