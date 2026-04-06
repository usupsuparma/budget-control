# Planning Implementation: Restrict Program ID by User Division

## 1. Analisis & Identifikasi Masalah
Pada form **Add Submission** di halaman **User Submission** (`http://127.0.0.1:8001/admission/user`), dropdown **Program ID** saat ini menampilkan seluruh `KPIWorkPlan` alias Program Kerja dari berbagai macam divisi. Hal ini bisa menyebabkan user salah memilih Program ID milik divisi lain, atau sengaja mengajukan *budget* pada *workplan* yang bukan wewenangnya. 

Sesuai aturan di **`EMPLOYEE_ORG_RESOLUTION.md`**, struktur hierarki organisasi dan departemen/divisi user diketahui secara dinamis dari `job_level_id` dan `structure_id` (berada di dalam `App\Models\Employment`). Oleh karena itu, kita perlu melengkapi Model `Employment` dengan fitur yang dapat melacak ID Divisi (Division ID) si user, dan menjadikan Division ID tersebut sebagai basis scope (filter) saat me-load `KPIWorkPlan`.

---

## 2. Rencana Eksekusi (Langkah demi Langkah)

### A. Update Model `Employment` (`app/Models/Employment.php`)
Tambahkan method baru bernama `getDivisionIds()` di dalam model `Employment` yang bertugas me-resolve Divisi ID dari User berdasarkan Job Level-nya. Method ini mirip konsepnya dengan `getDepartmentCodes()`.

**Kode Referensi:**
```php
public function getDivisionIds(): array
{
    $jobPosition = $this->jobPosition;

    if (! $jobPosition || ! $jobPosition->structure_id) {
        return [];
    }

    $levelId     = (int) $jobPosition->job_level_id;
    $structureId = (int) $jobPosition->structure_id;

    switch ($levelId) {
        case 1: // Director (Membawahi bbrp divisi)
            return \App\Models\Division::where('director_id', $structureId)->pluck('id')->toArray();

        case 2: // Division
            return [$structureId];

        case 3: // Department (cari divisi-nya lewat relasi department)
            $dept = \App\Models\Department::find($structureId);
            return ($dept && $dept->division_id) ? [$dept->division_id] : [];

        default: // Section / Staff / Non-Staff (cari divisi-nya lewat section -> department)
            $section = \App\Models\Section::with('department')->find($structureId);
            return ($section && $section->department && $section->department->division_id) 
                ? [$section->department->division_id] 
                : [];
    }
}
```

### B. Update Model `KPIWorkPlan` (`app/Models/KPIWorkPlan.php`)
Tambahkan *Query Scope* lokal di `KPIWorkPlan` agar logika filter pada Service lebih bersih (memenuhi best-practice "Skinny Controller/Service, Fat Model"). 

**Kode Referensi:**
```php
public function scopeWhereDivisionIn($query, array $divisionIds)
{
    return $query->where(function ($q) use ($divisionIds) {
        // Jika kpi_type = 'department'
        $q->whereHas('kpiDepartment.department', function ($subQ) use ($divisionIds) {
            $subQ->whereIn('division_id', $divisionIds);
        })
        // Atau jika kpi_type = 'section'
        ->orWhereHas('kpiSection.section.department', function ($subQ) use ($divisionIds) {
            $subQ->whereIn('division_id', $divisionIds);
        });
    });
}
```

### C. Refactor `SubmissionServiceImpl` (`app/Services/SubmissionService/SubmissionServiceImpl.php`)
Di sinilah data Program disalurkan baik untuk inisialisasi Modal maupun saat *Ajax Request* pada Dropdown. Modifikasi logika pengambilan `KPIWorkPlan`.

**1. Di dalam `getUserPageData()` (Sekitar Line 41):**
Ubah bagian pemanggilan raw `KPIWorkPlan::with(...)` menjadi ber-filter `whereDivisionIn()`.
```php
public function getUserPageData(): array
{
    // ... logic existing (auth user dll)
    $employment = Auth::user()->employment;
    $divisionIds = $employment ? $employment->getDivisionIds() : [];

    // Terapkan filter Divisi di scope Model
    $workplans = KPIWorkPlan::with(['kpiDepartment', 'kpiSection'])
        ->whereDivisionIn($divisionIds)
        ->get();

    // ... sisa logic return compact(...)
}
```

**2. Di dalam `getProgramsByJobLevel()` (Penting agar dropdown AJAX ter-restrict security-nya):**
```php
public function getProgramsByJobLevel(int $jobLevelId): array
{
    // ... logic pengecekan level & scope kpi_type (department/section)
    
    // Tarik list divisi wewenang saat ini
    $employment = Auth::user()->employment;
    $divisionIds = $employment ? $employment->getDivisionIds() : [];

    // Aplikasikan scope untuk restriction query
    $query = KPIWorkPlan::with(['kpiDepartment.department', 'kpiSection.section'])
        ->whereDivisionIn($divisionIds)
        ->orderBy('year', 'desc')
        ->orderBy('activity');

    // ... terapkan existing conditional if ($kpiType) dsb
    // ... return response data
}
```

### D. File-File Lain (Review Only)
- `routes/web.php` dan `SubmissionController.php`: Tidak perlu mengubah `Controller` dan `Route`, biarkan `Controller` hanya bertugas mendelegasikan request secara *orchestration*.
- `resources/views/pages/submission/user.blade.php`: Sistem loop `@foreach($workplans)` otomatis mendukung data API filter dari Service. **UI Aman, Tidak Perlu Sentuh Blade.**
- `ApprovalTransactionService.php` / `LpjService.php`: Validasi restriction program ini bersifat front-facing dan scope filtering. Untuk approval atau LPJ lanjutan dari ID transaksi yang masuk akal, ia akan terisolir. Relasi tetap aman.

---

## 3. Checklist QA & Testing

1. [ ] Login di satu environment (dummy local) dengan akun bertipe **Division Head** atau **Staff** di suatu divisi (misal: Divisi IT).
2. [ ] Masuk ke menu `http://127.0.0.1:8001/admission/user` > Klik `Add Data`.
3. [ ] Buka Dropdown `Program ID`, pastikan secara visual HANYA terdapat program/kpi di bawah divisinya saja.
4. [ ] Lakukan inspect/trigger element API ajax secara manual untuk job_level_id, pastikan response JSON yang dikembalikan juga tidak membocorkan Program ID divisi lain (Sudah dijaga melalui AJAX scope `getProgramsByJobLevel`).
