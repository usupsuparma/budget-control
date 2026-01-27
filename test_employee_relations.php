<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Employee;
use App\Models\Employment;

echo "=== Test Employee Relations ===\n\n";

// Test 1: Basic counts
echo "Test 1: Basic counts\n";
echo "Employee count: " . Employee::count() . "\n";
echo "Employment count: " . Employment::count() . "\n";
echo "\n";

// Test 2: Get first employee with employment
echo "Test 2: Employee with Employment\n";
$emp = Employee::with(['employment.jobPosition', 'employment.jobLevel', 'roles'])->first();

if ($emp) {
    echo "✓ Employee found!\n";
    echo "  - Name: {$emp->name}\n";
    echo "  - NIP (employee_code): " . ($emp->employee_code ?? 'NULL') . "\n";
    echo "  - Email: {$emp->email}\n";
    echo "  - Has employment: " . ($emp->employment ? 'Yes' : 'No') . "\n";
    
    if ($emp->employment) {
        echo "  - Job Position: " . ($emp->employment->jobPosition?->job_position_name ?? 'NULL') . "\n";
        echo "  - Job Level: " . ($emp->employment->jobLevel?->job_level_name ?? 'NULL') . "\n";
    }
    
    echo "  - Role: " . ($emp->roles->first()?->name ?? 'NULL') . "\n";
} else {
    echo "✗ No employee found\n";
}

echo "\n";

// Test 3: Test HasOneThrough relationship on Employee
echo "Test 3: Test jobPosition() hasOneThrough relationship\n";
$emp2 = Employee::with('employment')->first();
if ($emp2) {
    // Test direct hasOneThrough
    $jobPos = $emp2->jobPosition()->first();
    if ($jobPos) {
        echo "✓ HasOneThrough works!\n";
        echo "  - Job Position (via hasOneThrough): {$jobPos->job_position_name}\n";
    } else {
        echo "✗ HasOneThrough returned NULL\n";
    }
}

echo "\n=== Test Complete ===\n";
