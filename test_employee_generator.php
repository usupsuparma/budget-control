<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;

echo "=== Test Employee ID Generator ===\n\n";

// Test 1: Buat employee baru
echo "Test 1: Membuat employee baru...\n";
$employee = Employee::create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe@example.com',
    'password' => bcrypt('password123'),
    'status' => 'active'
]);

echo "✓ Employee berhasil dibuat!\n";
echo "  - ID: {$employee->id}\n";
echo "  - Employee ID: {$employee->employee_id}\n";
echo "  - Name: {$employee->name}\n";
echo "  - Email: {$employee->email}\n\n";

// Test 2: Cek apakah employment otomatis dibuat
echo "Test 2: Cek employment otomatis dibuat...\n";
// Refresh employee untuk mendapatkan relasi terbaru
$employee->refresh();
$employment = $employee->employment()->first();
if ($employment) {
    echo "✓ Employment berhasil dibuat otomatis!\n";
    echo "  - Employment ID: {$employment->id}\n";
    echo "  - Employee ID: {$employment->employee_id}\n";
    echo "  - Status: {$employment->status}\n\n";
} else {
    echo "✗ Employment tidak dibuat otomatis!\n\n";
}

// Test 3: Buat employee kedua untuk test increment
echo "Test 3: Membuat employee kedua...\n";
$employee2 = Employee::create([
    'first_name' => 'Jane',
    'last_name' => 'Smith',
    'email' => 'jane.smith@example.com',
    'password' => bcrypt('password123'),
    'status' => 'active'
]);

echo "✓ Employee kedua berhasil dibuat!\n";
echo "  - ID: {$employee2->id}\n";
echo "  - Employee ID: {$employee2->employee_id}\n";
echo "  - Name: {$employee2->name}\n\n";

// Test 4: Verifikasi employee_id berbeda
echo "Test 4: Verifikasi uniqueness...\n";
if ($employee->employee_id !== $employee2->employee_id) {
    echo "✓ Employee ID berbeda dan unique!\n";
    echo "  - Employee 1: {$employee->employee_id}\n";
    echo "  - Employee 2: {$employee2->employee_id}\n\n";
} else {
    echo "✗ Employee ID sama! Ada masalah dengan generator.\n\n";
}

// Cleanup - hapus data test
echo "Cleanup: Menghapus data test...\n";
$employee->forceDelete();
$employee2->forceDelete();
echo "✓ Data test berhasil dihapus.\n\n";

echo "=== Test Selesai ===\n";
