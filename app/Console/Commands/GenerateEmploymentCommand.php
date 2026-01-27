<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Employment;
use App\Models\JobLevel;
use App\Models\JobPosition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateEmploymentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:generate-employment 
                            {--dry-run : Preview changes without actually making them}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Employment records for existing Employees and auto-generate employee_code (NIP) if missing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Get all employees without employment
        $employeesWithoutEmployment = Employee::whereDoesntHave('employment')->get();
        $employeesWithoutNIP = Employee::whereNull('employee_code')->orWhere('employee_code', '')->get();

        $this->info('📊 Current Status:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Employees', Employee::count()],
                ['Employees without Employment', $employeesWithoutEmployment->count()],
                ['Employees without NIP (employee_code)', $employeesWithoutNIP->count()],
                ['Existing Employment records', Employment::count()],
            ]
        );

        if ($employeesWithoutEmployment->isEmpty() && $employeesWithoutNIP->isEmpty()) {
            $this->info('✅ All employees already have Employment records and NIP. Nothing to do!');
            return Command::SUCCESS;
        }

        if (!$isDryRun && !$this->option('force')) {
            if (!$this->confirm('Do you want to proceed with generating missing data?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        // Get master data for employment
        $jobLevels = JobLevel::where('status', 'Active')->orWhereNull('status')->get();
        $jobPositions = JobPosition::where('status', 'Active')->orWhereNull('status')->get();

        if ($jobLevels->isEmpty()) {
            $this->error('❌ No active JobLevel found! Please seed JobLevel data first.');
            return Command::FAILURE;
        }

        if ($jobPositions->isEmpty()) {
            $this->error('❌ No active JobPosition found! Please seed JobPosition data first.');
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('🚀 Starting generation process...');
        $this->newLine();

        $nipGenerated = 0;
        $employmentCreated = 0;
        $errors = [];

        if (!$isDryRun) {
            DB::beginTransaction();
        }

        try {
            // ========================================
            // STEP 1: Generate NIP (employee_code) for employees without it
            // ========================================
            $this->info('📝 Step 1: Generating employee_code (NIP) for employees without it...');
            
            $bar = $this->output->createProgressBar($employeesWithoutNIP->count());
            $bar->start();

            foreach ($employeesWithoutNIP as $employee) {
                $nip = $this->generateEmployeeCode($employee);
                
                if (!$isDryRun) {
                    $employee->update(['employee_code' => $nip]);
                }
                
                $nipGenerated++;
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
            $this->info("✅ Generated {$nipGenerated} employee_code (NIP)");

            // ========================================
            // STEP 2: Create Employment records for employees without it
            // ========================================
            $this->newLine();
            $this->info('📝 Step 2: Creating Employment records for employees without it...');
            
            // Re-fetch to get fresh data
            $employeesWithoutEmployment = Employee::whereDoesntHave('employment')->get();
            
            $bar = $this->output->createProgressBar($employeesWithoutEmployment->count());
            $bar->start();

            foreach ($employeesWithoutEmployment as $employee) {
                // Get job position info if available
                $jobPosition = $employee->jobPosition;
                
                // Default job level and position (will be overridden if employee has job_position)
                $jobLevelId = $jobPosition?->job_level_id ?? $jobLevels->random()->id;
                $jobLevelName = $jobPosition?->job_level_name ?? JobLevel::find($jobLevelId)?->job_level_name;
                $jobPositionId = $jobPosition?->id ?? $jobPositions->random()->id;
                $jobPositionName = $jobPosition?->job_position_name ?? JobPosition::find($jobPositionId)?->job_position_name;
                
                $employmentData = [
                    'employee_id' => $employee->id, // FK to employee.id
                    'organization_id' => $jobPosition?->organization_id ?? null,
                    'organization_name' => $jobPosition?->organization?->organization_name ?? null,
                    'job_level_id' => $jobLevelId,
                    'job_level_name' => $jobLevelName,
                    'job_position_id' => $jobPositionId,
                    'job_position_name' => $jobPositionName,
                    'uppline_id' => null,
                    'uppline_id_name' => null,
                    'employment_status' => 'Permanent',
                    'join_date' => $employee->created_at ?? now(),
                    'status' => $employee->status ?? 'Active',
                ];

                if (!$isDryRun) {
                    Employment::create($employmentData);
                }
                
                $employmentCreated++;
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
            $this->info("✅ Created {$employmentCreated} Employment records");

            if (!$isDryRun) {
                DB::commit();
            }

            // ========================================
            // SUMMARY
            // ========================================
            $this->newLine();
            $this->info('📊 Summary:');
            $this->table(
                ['Action', 'Count'],
                [
                    ['NIP (employee_code) generated', $nipGenerated],
                    ['Employment records created', $employmentCreated],
                ]
            );

            if ($isDryRun) {
                $this->newLine();
                $this->warn('⚠️  This was a DRY RUN. No changes were made.');
                $this->info('Run without --dry-run to apply changes.');
            } else {
                $this->newLine();
                $this->info('✅ All operations completed successfully!');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            if (!$isDryRun) {
                DB::rollBack();
            }
            
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            
            return Command::FAILURE;
        }
    }

    /**
     * Generate employee_code (NIP) dengan format berdasarkan birth_date
     * Format: YYMM + YY (tahun lahir) + NNNN (urutan)
     * Contoh: 2601930001 = tahun 2026, bulan 01, tahun lahir 1993, urutan 0001
     * 
     * Jika tidak ada birth_date, gunakan format: EMP-YYYYMMDD-XXXX
     */
    private function generateEmployeeCode(Employee $employee): string
    {
        $currentDate = now();
        
        // Jika employee punya birth_date, gunakan format NIP tradisional
        if ($employee->birth_date) {
            $yearMonth = $currentDate->format('ym'); // YYMM: 2601 untuk Jan 2026
            $birthYear = date('y', strtotime($employee->birth_date)); // 2 digit tahun lahir
            $prefix = $yearMonth . $birthYear;
            
            // Cari NIP terakhir dengan prefix yang sama
            $lastEmployee = Employee::where('employee_code', 'like', $prefix . '%')
                ->orderBy('employee_code', 'desc')
                ->first();
            
            if ($lastEmployee && $lastEmployee->employee_code) {
                $lastNumber = (int) substr($lastEmployee->employee_code, -4);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            
            return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        }
        
        // Fallback: gunakan format EMP-YYYYMMDD-XXXX
        $date = $currentDate->format('Ymd');
        $prefix = 'EMP-' . $date . '-';
        
        // Cari employee_code terakhir dengan prefix yang sama
        $lastEmployee = Employee::where('employee_code', 'like', $prefix . '%')
            ->orderBy('employee_code', 'desc')
            ->first();
        
        if ($lastEmployee && $lastEmployee->employee_code) {
            $lastNumber = (int) substr($lastEmployee->employee_code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
