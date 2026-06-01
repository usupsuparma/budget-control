# Employee Division Display Fix

Dokumentasi ini menjelaskan dua bug yang ditemukan dan diperbaiki terkait tampilan kolom **Division** pada tabel karyawan (Master Data → Employee), beserta solusi yang diimplementasikan.

---

## Bug 1: `JobPosition::structure()` Menggunakan FK yang Salah

### Deskripsi

Relasi `structure()` pada model `JobPosition` menggunakan `job_level_id` sebagai foreign key ke tabel `division`, padahal seharusnya menggunakan `structure_id`.

**File:** `app/Models/JobPosition.php`

**Sebelum (salah):**
```php
public function structure()
{
    return $this->belongsTo(Division::class, 'job_level_id', 'id');
}
```

**Sesudah (benar):**
```php
public function structure()
{
    return $this->belongsTo(Division::class, 'structure_id', 'id');
}
```

### Dampak

Karena `job_level_id` adalah ID level jabatan (contoh: `2` = Division Manager), query mencari Division dengan `id = 2` — bukan Division yang sebenarnya berkaitan dengan posisi tersebut. Hasilnya: semua karyawan yang kebetulan memiliki `job_level_id` yang nilainya cocok dengan ID sebuah Division lain akan ditampilkan Division yang salah.

**Contoh kasus:** Didin Nurdin (Marketing Division Manager) ditampilkan sebagai Finance Division karena `job_level_id = 2` secara kebetulan sama dengan `id` dari Finance Division.

---

## Bug 2: `JobPosition::structure()` Tidak Level-Aware (Tetap Salah untuk L3+)

### Deskripsi

Meskipun Bug 1 diperbaiki, relasi `structure()` masih selalu me-resolve ke `Division`. Ini benar untuk **L2 (Division Manager)**, tetapi **salah untuk L3 ke atas**:

| Job Level | `structure_id` Merujuk ke | Relasi `structure()` membaca |
|-----------|---------------------------|------------------------------|
| L1 Director | `director.id` | Division (salah) |
| L2 Division | `division.id` | Division (benar) |
| L3 Department | `department.id` | Division dengan `id = department_id` (salah) |
| L4+ Section | `section.id` | Division dengan `id = section_id` (salah) |

**Contoh kasus:** Ucu Karnati (Marketing Department Manager, L3) memiliki `structure_id` = ID dari Marketing Department. Query `Division::find(structure_id)` menemukan Division lain yang ID-nya kebetulan sama → ditampilkan Plant Division.

---

## Solusi: `Employment::getDivisionName()`

Ditambahkan method `getDivisionName()` pada `App\Models\Employment` yang melakukan traversal hierarki secara level-aware untuk menghasilkan nama Division yang benar pada semua level jabatan.

**File:** `app/Models/Employment.php`

```php
public function getDivisionName(): string
{
    $jobPosition = $this->jobPosition;

    if (! $jobPosition || ! $jobPosition->structure_id) {
        return '-';
    }

    $levelId     = (int) $jobPosition->job_level_id;
    $structureId = (int) $jobPosition->structure_id;

    switch ($levelId) {
        case 1: // Director → first division under this director
            $div = Division::where('director_id', $structureId)->orderBy('name')->first();
            return $div ? $div->name : '-';

        case 2: // Division → structure_id IS the division
            $div = Division::find($structureId);
            return $div ? $div->name : '-';

        case 3: // Department → get parent division
            $dept = Department::find($structureId);
            if (! $dept || ! $dept->division_id) return '-';
            $div = Division::find($dept->division_id);
            return $div ? $div->name : '-';

        default: // Section (4), Staff, Non-Staff → section → department → division
            $section = Section::find($structureId);
            if (! $section || ! $section->department_id) return '-';
            $dept = Department::find($section->department_id);
            if (! $dept || ! $dept->division_id) return '-';
            $div = Division::find($dept->division_id);
            return $div ? $div->name : '-';
    }
}
```

### Mapping Hierarki `getDivisionName()`

| Job Level | `structure_id` Merujuk ke | Traversal | Hasil |
|-----------|---------------------------|-----------|-------|
| L1 Director | `director.id` | Division pertama di bawah director tersebut | Nama Division |
| L2 Division | `division.id` | Langsung | Nama Division |
| L3 Department | `department.id` | Department → `division_id` | Nama Division induk |
| L4+ Section/Staff | `section.id` | Section → `department_id` → `division_id` | Nama Division induk |

---

## Perubahan pada Controller

**File:** `app/Http/Controllers/EmployeeController.php`

Kolom `division` pada DataTable diubah dari penggunaan `jobPosition->structure->name` (level-unaware) ke `employment->getDivisionName()` (level-aware):

**Sebelum:**
```php
->addColumn('division', function ($row) {
    $structure = $row->employment?->jobPosition?->structure;
    $divisionName = $structure?->name ?? '-';
    return '<span class="text-primary">' . e($divisionName) . '</span>';
})
```

**Sesudah:**
```php
->addColumn('division', function ($row) {
    $divisionName = $row->employment?->getDivisionName() ?? '-';
    return '<span class="text-primary">' . e($divisionName) . '</span>';
})
```

Eager loading juga diperbarui — `employment.jobPosition.structure` dihapus karena `getDivisionName()` mengelola query-nya sendiri:

```php
// Sebelum
Employee::with(['roles', 'employment.jobPosition.structure', 'employment.jobLevel'])

// Sesudah
Employee::with(['roles', 'employment.jobPosition', 'employment.jobLevel'])
```

---

## Aturan Turunan (Jangan Diulangi)

> **DILARANG** menggunakan `$jobPosition->structure` (relasi `JobPosition::structure()`) secara langsung untuk menampilkan nama Division. Relasi ini hanya valid untuk L2. Selalu gunakan `Employment::getDivisionName()` atau logika level-aware yang setara.

---

## File yang Diubah

| File | Perubahan |
|------|-----------|
| `app/Models/JobPosition.php` | FK relasi `structure()` diperbaiki dari `job_level_id` ke `structure_id` |
| `app/Models/Employment.php` | Tambah method `getDivisionName()` dengan traversal level-aware |
| `app/Http/Controllers/EmployeeController.php` | Kolom `division` DataTable menggunakan `getDivisionName()` |
