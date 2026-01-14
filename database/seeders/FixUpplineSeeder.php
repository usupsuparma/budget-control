<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Employment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixUpplineSeeder extends Seeder
{
    /**
     * Fix uppline_id untuk employment records yang belum punya uppline.
     */
    public function run(): void
    {
        $this->command->info('Fixing uppline_id for employment records...');
        
        DB::beginTransaction();
        
        try {
            $employments = Employment::with('employee')->get();
            $upplineAssigned = 0;
            
            foreach ($employments as $employment) {
                // Skip jika sudah punya uppline atau job level paling tinggi (Director = 1)
                if ($employment->uppline_id || $employment->job_level_id <= 1) {
                    continue;
                }
                
                // Cari potential uppline: employee dengan job level lebih tinggi (id lebih kecil = level lebih tinggi)
                $potentialUppline = Employment::with('employee')
                    ->where('job_level_id', '<', $employment->job_level_id)
                    ->where('id', '!=', $employment->id)
                    ->inRandomOrder()
                    ->first();
                
                if ($potentialUppline && $potentialUppline->employee) {
                    $employment->update([
                        'uppline_id' => $potentialUppline->employee->id,
                        'uppline_id_name' => $potentialUppline->employee->name,
                    ]);
                    $upplineAssigned++;
                }
            }
            
            DB::commit();
            
            $this->command->info("Assigned uppline for {$upplineAssigned} employees!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
