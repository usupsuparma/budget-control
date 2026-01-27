<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Employment;
use App\Models\JobLevel;
use App\Models\JobPosition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmploymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Format NIP: YYMM + YY (tahun lahir) + NNNN (urutan)
     * Contoh: 2203930001 = tahun 2022, bulan 03, tahun lahir 1993, urutan 0001
     */
    public function run(): void
    {
        // Ambil data master
        $jobLevels = JobLevel::where('status', 'Active')->orWhereNull('status')->get();
        $jobPositions = JobPosition::where('status', 'Active')->orWhereNull('status')->get();
        
        if ($jobLevels->isEmpty()) {
            $this->command->error('Tidak ada data JobLevel!');
            return;
        }
        
        if ($jobPositions->isEmpty()) {
            $this->command->error('Tidak ada data JobPosition!');
            return;
        }

        // Ambil semua employee
        $employees = Employee::with('employment')->get();
        
        $counter = 1; // Counter untuk urutan NIP
        $currentDate = now();
        $yearMonth = $currentDate->format('ym'); // YYMM: 2601 untuk Jan 2026
        
        DB::beginTransaction();
        
        try {
            // ========================================
            // PASS 1: Generate NIP dan Employment (tanpa uppline)
            // ========================================
            $this->command->info('Pass 1: Generating NIP and Employment records...');
            
            foreach ($employees as $employee) {
                // Generate birth year random antara 1980-2000 jika belum ada
                $birthYear = $employee->birth_year ?? rand(1980, 2000);
                $birthYearShort = substr($birthYear, -2); // Ambil 2 digit terakhir
                
                // Generate NIP (employee_code): YYMM + YY (birth) + NNNN (urutan)
                $nip = sprintf('%s%s%04d', $yearMonth, $birthYearShort, $counter);
                
                // Update employee dengan employee_code (NIP) dan birth_year
                $employee->update([
                    'employee_code' => $nip, // NIP (Nomor Induk Pegawai)
                    'birth_year' => $birthYear,
                ]);
                
                // Cek apakah sudah punya employment record (FK = employee.id)
                $hasEmployment = Employment::where('employee_id', $employee->id)->exists();
                
                if (!$hasEmployment) {
                    // Ambil random job level dan position
                    $randomJobLevel = $jobLevels->random();
                    $randomJobPosition = $jobPositions->random();
                    
                    // Buat employment record (employee_id = FK ke employee.id)
                    Employment::create([
                        'employee_id' => $employee->id, // FK ke employee.id
                        'organization_id' => null,
                        'organization_name' => null,
                        'job_level_id' => $randomJobLevel->id,
                        'job_level_name' => $randomJobLevel->job_level_name,
                        'job_position_id' => $randomJobPosition->id,
                        'job_position_name' => $randomJobPosition->job_position_name ?? null,
                        'uppline_id' => null, // Akan diisi di pass 2
                        'uppline_id_name' => null,
                        'employment_status' => 'Permanent',
                        'status' => 'Aktif',
                        'join_date' => $currentDate->copy()->subYears(rand(1, 10))->format('Y-m-d'),
                    ]);
                }
                
                $counter++;
            }
            
            // ========================================
            // PASS 2: Assign Uppline berdasarkan Job Level hierarchy
            // ========================================
            $this->command->info('Pass 2: Assigning Uppline based on Job Level hierarchy...');
            
            $employments = Employment::with('employee')->get();
            $upplineAssigned = 0;
            
            foreach ($employments as $employment) {
                // Skip jika sudah punya uppline atau job level paling tinggi (Director)
                if ($employment->uppline_id || $employment->job_level_id <= 1) {
                    continue;
                }
                
                // Cari potential uppline: employee dengan job level lebih tinggi (id lebih kecil)
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
            
            $this->command->info("Pass 1: Generated NIP for {$counter} employees!");
            $this->command->info("Pass 2: Assigned uppline for {$upplineAssigned} employees!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
