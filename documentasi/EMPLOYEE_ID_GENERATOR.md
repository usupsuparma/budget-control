# Auto-Generate Employee ID System

## Overview
Sistem ini mengatur generate otomatis untuk `employee_id` yang digunakan sebagai relasi antara tabel `employee` dan `employment`. Setiap kali Employee baru dibuat, sistem akan:

1. Generate `employee_id` unik dengan format `EMP-YYYYMMDD-XXXX`
2. Otomatis membuat record `Employment` dengan `employee_id` yang sama
3. Menjaga konsistensi data antara kedua tabel

## Format Employee ID
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
  `employee_id` varchar(50) NULL UNIQUE,  -- Generated automatically
  `email` varchar(100) NULL,
  `password` varchar(100) NULL,
  `remember_token` varchar(100) NULL,
  `first_name` varchar(100) NULL,
  `last_name` varchar(100) NULL,
  `role_id` int NULL,
  `job_position_id` int NULL,
  `status` varchar(100) NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  `deleted_at` timestamp NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_employee_id_unique` (`employee_id`)
);
```

### Tabel Employment
```sql
CREATE TABLE `employment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) NULL,  -- FK to employee.employee_id
  `organization_id` varchar(100) NULL,
  `organization_name` varchar(100) NULL,
  `job_level_id` varchar(100) NULL,
  `job_level_name` varchar(100) NULL,
  `job_position_id` varchar(100) NULL,
  `job_position_name` varchar(100) NULL,
  `uppline_id` int NULL,
  `uppline_id_name` varchar(100) NULL,
  `employment_status` varchar(100) NULL,
  `role_id` int NULL,
  `role_name` varchar(100) NULL,
  `status` varchar(100) NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  `deleted_at` timestamp NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`employee_id`) REFERENCES `employee`(`employee_id`) ON DELETE CASCADE
);
```

## Relasi Model

### Employee Model
```php
public function employment()
{
    return $this->hasOne(Employment::class, 'employee_id', 'employee_id');
}
```

### Employment Model
```php
public function employee()
{
    return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
}
```

## Cara Kerja Observer

### EmployeeObserver
Observer ini menangani event-event pada Employee model:

1. **creating** - Generate `employee_id` sebelum disimpan ke database
2. **created** - Buat record Employment otomatis
3. **updated** - Sync perubahan `employee_id` ke Employment (jika ada)
4. **restored** - Restore Employment yang terkait
5. **forceDeleted** - Force delete Employment yang terkait

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

// employee_id akan di-generate otomatis
echo $employee->employee_id; // Output: EMP-20260112-0001

// Employment juga dibuat otomatis
$employment = $employee->employment;
echo $employment->employee_id; // Output: EMP-20260112-0001
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
1. `2026_01_12_150054_add_employee_id_to_employee_table.php` - Menambahkan kolom employee_id dan unique constraint
2. `2026_01_12_150237_add_foreign_key_to_employment_table.php` - Mengubah tipe data dan menambahkan foreign key

### Observer
- `app/Observers/EmployeeObserver.php` - Observer untuk handle auto-generate employee_id dan lifecycle

### Provider
- `app/Providers/AppServiceProvider.php` - Mendaftarkan EmployeeObserver

## Testing
Jalankan script test untuk verifikasi:
```bash
php test_employee_generator.php
```

Output yang diharapkan:
```
=== Test Employee ID Generator ===

Test 1: Membuat employee baru...
✓ Employee berhasil dibuat!
  - ID: 242
  - Employee ID: EMP-20260112-0003
  - Name: John Doe
  - Email: john.doe@example.com

Test 2: Cek employment otomatis dibuat...
✓ Employment berhasil dibuat otomatis!
  - Employment ID: 15
  - Employee ID: EMP-20260112-0003
  - Status: active

Test 3: Membuat employee kedua...
✓ Employee kedua berhasil dibuat!
  - ID: 243
  - Employee ID: EMP-20260112-0004
  - Name: Jane Smith

Test 4: Verifikasi uniqueness...
✓ Employee ID berbeda dan unique!
  - Employee 1: EMP-20260112-0003
  - Employee 2: EMP-20260112-0004

Cleanup: Menghapus data test...
✓ Data test berhasil dihapus.

=== Test Selesai ===
```

## Catatan Penting

1. **Employee ID Unik**: Setiap employee memiliki employee_id yang unik dengan format EMP-YYYYMMDD-XXXX
2. **Auto Increment**: Nomor urut akan reset setiap hari (berdasarkan tanggal)
3. **Relasi Cascade**: Ketika Employee dihapus (soft delete atau force delete), Employment terkait juga akan terhapus
4. **Restore**: Ketika Employee di-restore, Employment yang terhubung juga akan di-restore
5. **Foreign Key**: Employment.employee_id adalah foreign key ke Employee.employee_id (bukan Employee.id)

## Troubleshooting

### Error: Duplicate column name 'employee_id'
Kolom employee_id sudah ada di tabel. Skip migration atau rollback dulu.

### Error: Specified key was too long
Pastikan employee_id menggunakan varchar(50) bukan varchar(255).

### Employment tidak dibuat otomatis
Pastikan EmployeeObserver sudah terdaftar di AppServiceProvider.

### Foreign key constraint fails
Pastikan tipe data employee_id sama di kedua tabel (varchar(50)).
