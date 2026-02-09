<?php

namespace App\Console\Commands;

use App\Models\Employment;
use Illuminate\Console\Command;

class ScanCircularReferences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employment:scan-circular {--fix : Automatically fix self-references}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan employment records for circular references in uppline chain';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== SCANNING EMPLOYMENT RECORDS FOR CIRCULAR REFERENCES ===');
        $this->newLine();

        $allEmployments = Employment::whereNotNull('uppline_id')->get();
        
        $this->info("Total employments to check: {$allEmployments->count()}");
        $this->newLine();

        $circularRefs = [];
        $selfRefs = [];

        // Progress bar
        $bar = $this->output->createProgressBar($allEmployments->count());
        $bar->start();

        foreach ($allEmployments as $employment) {
            // Check self-reference
            if ($employment->uppline_id == $employment->employee_id) {
                $selfRefs[] = $employment;
                $bar->advance();
                continue;
            }
            
            // Check circular reference in chain
            $visited = [];
            $current = $employment;
            $depth = 0;
            $maxDepth = 100;
            
            while ($current && $current->uppline_id && $depth < $maxDepth) {
                $depth++;
                
                if (in_array($current->employee_id, $visited)) {
                    $circularRefs[] = [
                        'starting_employee_id' => $employment->employee_id,
                        'circular_at' => $current->employee_id,
                        'chain' => $visited
                    ];
                    break;
                }
                
                $visited[] = $current->employee_id;
                $current = Employment::where('employee_id', $current->uppline_id)->first();
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Report self-references
        if (count($selfRefs) > 0) {
            $this->error("⚠️  SELF-REFERENCES FOUND: " . count($selfRefs));
            $this->line('─────────────────────────────────────');
            
            $headers = ['Employee ID', 'Job Level', 'Status'];
            $rows = [];
            
            foreach ($selfRefs as $emp) {
                $rows[] = [
                    $emp->employee_id,
                    $emp->job_level_name ?? 'N/A',
                    $emp->status
                ];
            }
            
            $this->table($headers, $rows);
            
            // Fix if --fix option provided
            if ($this->option('fix')) {
                if ($this->confirm('Fix all self-references by setting uppline_id to NULL?', true)) {
                    $fixed = 0;
                    foreach ($selfRefs as $emp) {
                        $emp->uppline_id = null;
                        $emp->save();
                        $fixed++;
                    }
                    $this->info("✓ Fixed {$fixed} self-references");
                }
            } else {
                $this->warn('Run with --fix option to automatically fix self-references');
            }
            $this->newLine();
        } else {
            $this->info('✓ No self-references found');
            $this->newLine();
        }

        // Report circular references
        if (count($circularRefs) > 0) {
            $this->error("⚠️  CIRCULAR REFERENCES FOUND: " . count($circularRefs));
            $this->line('─────────────────────────────────────');
            
            foreach ($circularRefs as $ref) {
                $this->line("Starting from Employee ID: {$ref['starting_employee_id']}");
                $this->line("  Circular at: {$ref['circular_at']}");
                $this->line("  Chain: " . implode(' → ', $ref['chain']));
                $this->newLine();
            }
            
            $this->warn('⚠️  Manual intervention required for circular references.');
            $this->warn('   Please review the uppline chain data for these employees.');
            $this->newLine();
        } else {
            $this->info('✓ No circular references found');
            $this->newLine();
        }

        // Check for long chains
        $this->info('=== CHECKING FOR LONG CHAINS ===');
        $longChains = [];

        foreach ($allEmployments as $employment) {
            $depth = 0;
            $current = $employment;
            $visited = [];
            
            while ($current && $current->uppline_id && $depth < 100) {
                if (in_array($current->employee_id, $visited)) {
                    break; // Skip circular references (already reported)
                }
                $visited[] = $current->employee_id;
                $depth++;
                $current = Employment::where('employee_id', $current->uppline_id)->first();
            }
            
            if ($depth > 10) {
                $longChains[] = [
                    'employee_id' => $employment->employee_id,
                    'depth' => $depth
                ];
            }
        }

        if (count($longChains) > 0) {
            $this->warn("⚠️  LONG CHAINS FOUND (>10 levels): " . count($longChains));
            $this->line('─────────────────────────────────────');
            
            $headers = ['Employee ID', 'Chain Depth'];
            $rows = [];
            
            foreach ($longChains as $chain) {
                $rows[] = [$chain['employee_id'], $chain['depth']];
            }
            
            $this->table($headers, $rows);
            $this->warn('Note: Long chains may cause performance issues during login.');
            $this->newLine();
        } else {
            $this->info('✓ No unusually long chains found');
            $this->newLine();
        }

        $this->info('=== SCAN COMPLETE ===');
        
        return Command::SUCCESS;
    }
}
