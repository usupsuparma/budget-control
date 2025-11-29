# Work Plan (Program Kerja) Module

## Overview
Fitur Work Plan adalah sistem manajemen program kerja yang terintegrasi dengan KPI Department dan KPI Section. Setiap department dan section dapat memiliki multiple work plans dengan tracking budget dan realization per bulan.

## Struktur Data

### Database
- **Tabel**: `kpi_workplans`
- **Relationships**: 
  - Polymorphic relationship ke `kpi_department` atau `kpi_section`
  - Approved by → `employee`

### Fields
- `kpi_type`: department atau section
- `kpi_id`: ID dari KPI Department atau KPI Section
- `activity`: Nama aktivitas/program kerja
- `duration_days`: Durasi dalam hari
- `schedule_start/end`: Jadwal mulai dan selesai
- `plan_jan` - `plan_dec`: Planning per bulan (boolean)
- `real_jan` - `real_dec`: Realization per bulan (boolean)
- `budget`: Anggaran untuk work plan ini
- `status`: draft, pending, approved, rejected
- `approved_by`, `approved_at`: Info approval

## Fitur Utama

### 1. Filter & Load Data
- Pilih Division dan Year
- Load hierarchical data: Division → Department → Section
- Tampilkan existing workplans

### 2. Hierarchical Display
```
KPI DIVISION
  └── KPI DEPARTMENT A
      ├── Work Plans (dapat add, edit, delete)
      └── KPI SECTION A
          └── Work Plans (dapat add, edit, delete)
      └── KPI SECTION B
          └── Work Plans (dapat add, edit, delete)
  └── KPI DEPARTMENT B
      └── Work Plans
```

### 3. Dynamic Workplan Management
- **Add**: Tombol "Add Work Plan" untuk menambah row baru
- **Edit**: Edit existing workplan
- **Delete**: Hapus workplan dengan konfirmasi
- **Approve**: Approve workplan (status → approved, tidak bisa diedit lagi)

### 4. Planning & Realization
- Checkbox per bulan untuk planning (Activities)
- Checkbox per bulan untuk realization
- Budget input dengan format currency
- Duration calculation

## API Endpoints

### GET `/workplan`
Halaman utama work plan

### GET `/workplan/get-kpi-data`
**Parameters:**
- `division_id`: ID Division
- `year`: Tahun

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "division_goals": "...",
      "target_division": "...",
      "departments": [
        {
          "id": 1,
          "department_name": "...",
          "department_goals": "...",
          "workplans": [...],
          "sections": [
            {
              "id": 1,
              "section_name": "...",
              "workplans": [...]
            }
          ]
        }
      ]
    }
  ]
}
```

### POST `/workplan/store`
Simpan workplan baru

**Body:**
```json
{
  "kpi_type": "department|section",
  "kpi_id": 1,
  "year": 2025,
  "activity": "Nama aktivitas",
  "duration_days": 30,
  "schedule_start": "2025-01-01",
  "schedule_end": "2025-01-31",
  "plan_jan": 1,
  "plan_feb": 0,
  ...,
  "budget": 10000000,
  "description": "..."
}
```

### PUT `/workplan/{id}`
Update existing workplan (body sama seperti store)

### DELETE `/workplan/{id}`
Hapus workplan

### POST `/workplan/{id}/approve`
Approve workplan

## Frontend Implementation

### Files
1. **View**: `resources/views/pages/work-plan/work-plan.blade.php`
2. **JavaScript**: `public/assets/js/workplan.js`
3. **Controller**: `app/Http/Controllers/KPIWorkPlanController.php`
4. **Model**: `app/Models/KPIWorkPlan.php`

### JavaScript Functions
- `loadKpiData()`: Load data dari server
- `renderKpiData()`: Render hierarchical structure
- `renderWorkplanTable()`: Render table workplan
- `addWorkplanRow()`: Tambah row workplan baru
- `saveWorkplan()`: Save/update workplan
- `deleteWorkplan()`: Hapus workplan
- `approveWorkplan()`: Approve workplan
- `toggleSection()`: Expand/collapse section

### Styling
- Gray header untuk KPI Division
- Light gray untuk KPI Department
- Lighter gray untuk KPI Section
- Blue background untuk planning months
- Red background untuk realization months
- Yellow background untuk target/satuan cells

## Permission
Module ini menggunakan permission: `budget.view`

## Usage Flow

1. User membuka halaman `/workplan`
2. User memilih Division dan Year
3. Klik "Load KPI Data"
4. System menampilkan hierarchy KPI Division → Department → Section
5. User dapat expand/collapse setiap section
6. Pada setiap Department/Section, user dapat:
   - Klik "Add Work Plan" untuk menambah program kerja
   - Isi Activity, Duration, Planning months, Budget
   - Klik "Save" untuk menyimpan
   - Klik "Edit" untuk mengubah
   - Klik "Delete" untuk menghapus
   - Klik "Approve" untuk meng-approve (setelah approve tidak bisa diedit)
7. User dapat mengisi realization per bulan

## Notes
- Workplan hanya bisa dibuat untuk Department dan Section, tidak untuk Division
- Setelah approved, workplan tidak bisa diedit atau dihapus
- Budget di-format otomatis dengan thousand separator
- Duration bisa dihitung otomatis dari schedule_start dan schedule_end
- Setiap perubahan langsung disimpan ke database (no bulk save)
