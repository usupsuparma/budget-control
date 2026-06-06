# Auto-Generate Employee Code (NIP) System

## Overview
Sistem ini mengatur generate otomatis untuk `employee_code` (NIP/Nomor Induk Pegawai) pada tabel `employee`. Setiap kali Employee baru dibuat, sistem akan:

1. Generate `employee_code` unik dengan format `EMP-YYYYMMDD-XXXX`
2. Otomatis membuat record `Employment` dengan `employee_id` = `employee.id` (FK)
3. Menjaga konsistensi data antara kedua tabel

## Format Employee Code (NIP)
**Format:** `EMP-YYYYMMDD-XXXX`

- `EMP`: Prefix untuk employee
- `YYYYMMDD`: Tanggal pembuatan (tahun-bulan-tanggal)
- `XXXX`: Nomor urut 4 digit (0001, 0002, dst.)

**Contoh:**
- `EMP-20260112-0001` - Employee pertama dibuat pada 12 Januari 2026
- `EMP-20260112-0002` - Employee kedua dibuat pada hari yang sama
- `EMP-20260113-0001` - Employee pertama dibuat pada 13 Januari 2026

## Database Schema

### Tabel Employee
```sql
CREATE TABLE `employee` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_code` varchar(50) NULL UNIQUE,  -- NIP, Generated automatically
  `email` varchar(100) NULL,
  `password` varchar(100) NULL,
  `remember_token` varchar(100) NULL,
  `first_name` varchar(100) NULL,
  `last_name` varchar(100) NULL,
  `job_position_id` int NULL,
  `status` varchar(100) NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  `deleted_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_employee_code_unique` (`employee_code`)
);
```

### Tabel Employment
```sql
CREATE TABLE `employment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NULL,  -- FK to employee.id (integer, NOT NIP)
  `organization_id` varchar(100) NULL,
  `organization_name` varchar(100) NULL,
  `job_level_id` varchar(100) NULL,
  `job_level_name` varchar(100) NULL,
  `job_position_id` varchar(100) NULL,
  `job_position_name` varchar(100) NULL,
  `uppline_id` int NULL,
  `uppline_id_name` varchar(100) NULL,
  `employment_status` varchar(100) NULL,
  `status` varchar(100) NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  `deleted_at` timestamp NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`employee_id`) REFERENCES `employee`(`id`) ON DELETE CASCADE
);
```

## Relasi Model

### Employee Model
```php
/**
 * Get employee's employment record.
 * employment.employee_id (FK) references employee.id (PK)
 */
public function employment()
{
    return $this->hasOne(Employment::class, 'employee_id', 'id');
}
```

### Employment Model
```php
/**
 * Get the employee that owns this employment.
 * employment.employee_id (FK) references employee.id (PK)
 */
public function employee()
{
    return $this->belongsTo(Employee::class, 'employee_id', 'id');
}
```

## Cara Kerja Observer

### EmployeeObserver
Observer ini menangani event-event pada Employee model:

1. **creating** - Generate `employee_code` (NIP) sebelum disimpan ke database
2. **created** - Buat record Employment dengan `employee_id` = `employee.id` (FK)
3. **restored** - Restore Employment yang terkait
4. **forceDeleted** - Force delete Employment yang terkait

## Penggunaan

### Membuat Employee Baru
```php
use App\Models\Employee;

// Buat employee baru
$employee = Employee::create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe@example.com',
    'password' => bcrypt('password123'),
    'status' => 'active'
]);

// employee_code (NIP) akan di-generate otomatis
echo $employee->employee_code; // Output: EMP-20260112-0001

// Employment juga dibuat otomatis dengan employee_id = employee.id
$employment = $employee->employment;
echo $employment->employee_id; // Output: 1 (employee.id, bukan NIP)
```

### Mengakses Employment dari Employee
```php
$employee = Employee::find(1);
$employment = $employee->employment;

if ($employment) {
    echo "Organization: " . $employment->organization_name;
    echo "Job Position: " . $employment->job_position_name;
}
```

### Mengakses Employee dari Employment
```php
$employment = Employment::find(1);
$employee = $employment->employee;

if ($employee) {
    echo "Name: " . $employee->name;
    echo "Email: " . $employee->email;
}
```

### Update Employment Data
```php
$employee = Employee::find(1);
$employee->employment->update([
    'organization_id' => '001',
    'organization_name' => 'IT Department',
    'job_level_id' => '3',
    'job_level_name' => 'Senior',
    'job_position_id' => '10',
    'job_position_name' => 'Senior Developer',
    'employment_status' => 'Aktif',
    'status' => 'active'
]);
```

### Soft Delete
```php
$employee = Employee::find(1);
$employee->delete(); // Soft delete employee

// Employment akan otomatis di-soft delete karena cascade
```

### Restore
```php
$employee = Employee::withTrashed()->find(1);
$employee->restore(); // Restore employee

// Employment akan otomatis di-restore
```

### Force Delete
```php
$employee = Employee::withTrashed()->find(1);
$employee->forceDelete(); // Permanent delete

// Employment akan otomatis di-force delete
```

## Files Changed/Created

### Migrations
1. `2026_01_27_163127_alter_column_employee_id_in_employment_table.php` - Mengubah tipe data employment.employee_id dari varchar ke bigint dan migrasi nilai
2. `2026_01_27_163258_rename_column_employee_id_to_employee_code_in_employee_table.php` - Rename kolom employee_id ke employee_code
3. `2026_01_27_170000_add_foreign_key_employee_id_to_employment_table.php` - Menambahkan foreign key constraint

### Observer
- `app/Observers/EmployeeObserver.php` - Observer untuk handle auto-generate employee_code dan lifecycle

### Provider
- `app/Providers/AppServiceProvider.php` - Mendaftarkan EmployeeObserver

## Testing
Jalankan script test untuk verifikasi:
```bash
php test_employee_generator.php
```

Output yang diharapkan:
```
=== Test Employee Code Generator ===

Test 1: Membuat employee baru...
✓ Employee berhasil dibuat!
  - ID: 242
  - Employee Code (NIP): EMP-20260127-0001
  - Name: John Doe
  - Email: john.doe@example.com

Test 2: Cek employment otomatis dibuat...
✓ Employment berhasil dibuat otomatis!
  - Employment ID: 15
  - Employee ID (FK): 242 (references employee.id)
  - Status: active

Test 3: Membuat employee kedua...
✓ Employee kedua berhasil dibuat!
  - ID: 243
  - Employee Code (NIP): EMP-20260127-0002
  - Name: Jane Smith

Test 4: Verifikasi uniqueness...
✓ Employee Code berbeda dan unique!
  - Employee 1: EMP-20260127-0001
  - Employee 2: EMP-20260127-0002

Cleanup: Menghapus data test...
✓ Data test berhasil dihapus.

=== Test Selesai ===
```

## Catatan Penting

1. **Employee Code Unik**: Setiap employee memiliki employee_code (NIP) yang unik dengan format EMP-YYYYMMDD-XXXX
2. **Auto Increment**: Nomor urut akan reset setiap hari (berdasarkan tanggal)
3. **Relasi Cascade**: Ketika Employee dihapus (soft delete atau force delete), Employment terkait juga akan terhapus
4. **Restore**: Ketika Employee di-restore, Employment yang terhubung juga akan di-restore
5. **Foreign Key**: Employment.employee_id adalah foreign key ke Employee.id (integer, bukan NIP)

## Troubleshooting

### Error: Duplicate column name 'employee_code'
Kolom employee_code sudah ada di tabel. Skip migration atau rollback dulu.

### Employment tidak dibuat otomatis
Pastikan EmployeeObserver sudah terdaftar di AppServiceProvider.

### Foreign key constraint fails
Pastikan employment.employee_id berisi nilai integer yang valid (merujuk ke employee.id).
