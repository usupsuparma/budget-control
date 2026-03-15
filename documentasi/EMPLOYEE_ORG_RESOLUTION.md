# Resolusi Organisasi Karyawan (Division, Department, Section)

Dokumentasi ini menjelaskan logika bisnis untuk menentukan unit organisasi (Divisi, Departemen, atau Seksi) dari karyawan yang sedang login.

## Arsitektur Data

Resolusi organisasi bergantung pada relasi antara model-model berikut:
- **Employee**: Entitas utama pengguna.
- **Employment**: Menyimpan data kepegawaian, termasuk `job_level_id` dan `job_position_id`.
- **JobPosition**: Menghubungkan posisi dengan `structure_id` (ID dari unit organisasi terkait).
- **JobLevel**: Menentukan hirarki posisi (1: Director, 2: Division, 3: Department, 4+: Section/Staff).

## Logika Bisnis Resolusi (Mapping Hirarki)

Penentuan unit organisasi dilakukan berdasarkan `job_level_id` dan `structure_id` yang ada pada `job_position` karyawan.

### 1. Level 1: Director
- **Scope**: Seluruh divisi dan departemen yang berada di bawah direktur tersebut.
- **Logika**: 
  - `structure_id` pada `job_position` merujuk ke `director.id`.
  - Mengambil semua `division` yang memiliki `director_id` tersebut.
  - Mengambil semua `department` yang terhubung ke divisi-divisi tersebut.

### 2. Level 2: Division (Head of Division)
- **Scope**: Divisi tersebut dan seluruh departemen di dalamnya.
- **Logika**:
  - `structure_id` pada `job_position` merujuk ke `division.id`.
  - Mengambil semua `department` yang memiliki `division_id` tersebut.

### 3. Level 3: Department (Head of Department)
- **Scope**: Departemen spesifik tersebut.
- **Logika**:
  - `structure_id` pada `job_position` merujuk ke `department.id`.
  - Unit organisasi utama adalah departemen tersebut.

### 4. Level 4+: Section / Staff / Non-Staff
- **Scope**: Seksi spesifik dan departemen induknya.
- **Logika**:
  - `structure_id` pada `job_position` merujuk ke `section.id`.
  - Mengambil `department_id` dari model `Section` untuk mengetahui departemen induknya.

## Implementasi Kode (Model Employment)

Logika ini diimplementasikan dalam method `getDepartmentCodes()` pada `App\Models\Employment`:

```php
public function getDepartmentCodes(): array
{
    $jobPosition = $this->jobPosition;
    if (! $jobPosition || ! $jobPosition->structure_id) {
        return [];
    }

    $levelId     = (int) $jobPosition->job_level_id;
    $structureId = (int) $jobPosition->structure_id;

    switch ($levelId) {
        case 1: // Director
            $divisionIds = Division::where('director_id', $structureId)->pluck('id');
            return Department::whereIn('division_id', $divisionIds)->pluck('code')->toArray();

        case 2: // Division
            return Department::where('division_id', $structureId)->pluck('code')->toArray();

        case 3: // Department
            $dept = Department::find($structureId);
            return ($dept && $dept->code) ? [$dept->code] : [];

        default: // Section/Staff (via section -> department)
            $section = Section::find($structureId);
            return ($section && $section->department && $section->department->code) 
                ? [$section->department->code] 
                : [];
    }
}
```

## Cara Mengetahui Data Organisasi User Login

Untuk mendapatkan data organisasi dari user yang sedang login di Controller atau Livewire:

```php
$employee = auth()->user(); // Pastikan guard mengarah ke employee
$employment = $employee->employment;

// Mendapatkan list kode departemen yang menjadi wewenangnya
$deptCodes = $employment->getDepartmentCodes();

// Mendapatkan data posisi dan level
$positionName = $employment->job_position_name;
$levelName = $employment->job_level_name;
```

## Summary Pemetaan Hirarki
| Job Level | Structure ID Refers To | Parent Scope |
|-----------|-------------------------|--------------|
| 1 (Director) | Director ID | Division -> Department |
| 2 (Division) | Division ID | Division -> Department |
| 3 (Department) | Department ID | Department |
| 4 (Section) | Section ID | Department -> Section |
