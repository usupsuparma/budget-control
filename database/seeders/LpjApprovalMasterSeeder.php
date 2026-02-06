<?php

namespace Database\Seeders;

use App\Models\Employment;
use App\Models\LpjApprovalMaster;
use Illuminate\Database\Seeder;

class LpjApprovalMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates sample LPJ approvers.
     * Modify the employment IDs based on your actual data.
     */
    public function run(): void
    {
        // Clear existing data
        LpjApprovalMaster::truncate();

        // Get some employments to set as approvers
        // In production, you should configure this via admin panel
        $employments = Employment::with('employee')
            ->whereHas('employee', function($q) {
                // Get users with specific job levels (e.g., managers, directors)
                $q->whereHas('roles', function($r) {
                    $r->whereIn('name', ['admin', 'finance', 'director', 'manager']);
                });
            })
            ->take(3)
            ->get();

        $sequence = 1;
        foreach ($employments as $employment) {
            LpjApprovalMaster::create([
                'employment_id' => $employment->id,
                'approval_sequence' => $sequence++,
                'is_active' => true,
            ]);
        }

        // If no employments found from roles, use first few employments
        if ($sequence == 1) {
            $fallbackEmployments = Employment::take(2)->get();
            foreach ($fallbackEmployments as $employment) {
                LpjApprovalMaster::create([
                    'employment_id' => $employment->id,
                    'approval_sequence' => $sequence++,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('LPJ Approval Masters seeded with ' . ($sequence - 1) . ' approvers.');
    }
}
