# Work Plan Quick Setup

## Yang Sudah Dikerjakan ✅

1. ✅ **Database Migration** - `kpi_workplans` table
2. ✅ **Model** - `KPIWorkPlan.php` dengan relationships
3. ✅ **Controller** - `KPIWorkPlanController.php` dengan CRUD methods
4. ✅ **Routes** - Semua endpoint API sudah ditambahkan
5. ✅ **View** - `work-plan.blade.php` dengan hierarchical layout
6. ✅ **JavaScript** - `workplan.js` untuk dynamic interactions
7. ✅ **Sidebar Menu** - Menu "Work Plan" di bawah Budget Control
8. ✅ **Model Relations** - `KPIDepartment` dan `KPISection` → `workplans()`
9. ✅ **CSRF Token** - Sudah ditambahkan di master layout

## Testing Checklist

### 1. Persiapan Data
- [ ] Pastikan ada data KPI Division
- [ ] Pastikan ada data KPI Department
- [ ] Pastikan ada data KPI Section
- [ ] Pastikan user login punya permission `budget.view`

### 2. Akses Halaman
- [ ] Buka `/workplan`
- [ ] Pastikan filter Division dan Year muncul
- [ ] Pastikan dropdown Division terisi dengan data

### 3. Load Data
- [ ] Pilih Division
- [ ] Pilih Year
- [ ] Klik "Load KPI Data"
- [ ] Pastikan data KPI hierarchy muncul

### 4. Add Work Plan
- [ ] Klik "Expand" pada Department/Section
- [ ] Klik "Add Work Plan"
- [ ] Isi Activity
- [ ] Centang beberapa bulan planning
- [ ] Isi Budget
- [ ] Klik "Save"
- [ ] Pastikan berhasil tersimpan

### 5. Edit Work Plan
- [ ] Klik tombol "Edit" pada workplan
- [ ] Ubah data
- [ ] Klik "Save"
- [ ] Pastikan perubahan tersimpan

### 6. Delete Work Plan
- [ ] Klik tombol "Delete"
- [ ] Konfirmasi hapus
- [ ] Pastikan terhapus dari database

### 7. Approve Work Plan
- [ ] Klik tombol "Approve"
- [ ] Pastikan status berubah jadi "Approved"
- [ ] Pastikan tidak bisa diedit lagi

## Troubleshooting

### Error "Nothing to migrate"
✅ Migration sudah dijalankan sebelumnya, tidak perlu action

### Error 404 Not Found
- Cek apakah route sudah terdaftar: `php artisan route:list | grep workplan`
- Pastikan controller sudah di-import di `web.php`

### Error "Class KPIWorkPlan not found"
- Jalankan: `composer dump-autoload`

### JavaScript tidak jalan
- Periksa console browser (F12)
- Pastikan jQuery sudah dimuat
- Pastikan file `workplan.js` ter-load

### Data tidak muncul
- Periksa console browser untuk error AJAX
- Periksa network tab untuk response
- Pastikan ada data KPI Division, Department, Section di database

### Permission Denied
- Pastikan user punya permission `budget.view`
- Jalankan: `php artisan permission:cache-reset`

## Cara Menjalankan

```bash
# 1. Masuk ke directory project
cd /d/project/laravel/budgetControl

# 2. Jalankan migration (jika belum)
php artisan migrate

# 3. Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 4. Jalankan server
php artisan serve

# 5. Buka browser
http://localhost:8000/workplan
```

## File Locations

```
app/
├── Http/Controllers/
│   └── KPIWorkPlanController.php         # Controller
├── Models/
│   ├── KPIWorkPlan.php                   # Main Model
│   ├── KPIDepartment.php                 # Updated with relations
│   └── KPISection.php                    # Updated with relations
database/
└── migrations/
    └── 2025_11_27_153546_create_kpi_workplans_table.php
public/
└── assets/js/
    └── workplan.js                       # Frontend JS
resources/views/
├── layouts/
│   └── master.blade.php                  # Updated with CSRF
├── include/
│   └── sidebar.blade.php                 # Updated with menu
└── pages/work-plan/
    └── work-plan.blade.php               # Main view
routes/
└── web.php                               # Updated with routes
```

## Database Schema

```sql
kpi_workplans
├── id
├── kpi_type (enum: department, section)
├── kpi_id (foreign key)
├── year
├── activity
├── duration_days
├── schedule_start
├── schedule_end
├── plan_jan ... plan_dec (booleans)
├── budget (decimal)
├── real_jan ... real_dec (booleans)
├── status (enum: draft, pending, approved, rejected)
├── approved_by (foreign key to employee)
├── approved_at
├── description
├── notes
├── created_at
├── updated_at
└── deleted_at (soft delete)
```

## API Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/workplan` | Main page |
| GET | `/workplan/get-kpi-data` | Get KPI hierarchy data |
| POST | `/workplan/store` | Create new workplan |
| PUT | `/workplan/{id}` | Update workplan |
| DELETE | `/workplan/{id}` | Delete workplan |
| POST | `/workplan/{id}/approve` | Approve workplan |

## Support

Jika ada masalah, periksa:
1. Laravel log: `storage/logs/laravel.log`
2. Browser console (F12 → Console)
3. Network tab untuk AJAX errors
4. Database connection
5. Permission settings

---
**Created**: 27 November 2025
**Version**: 1.0
**Laravel**: 12.x
