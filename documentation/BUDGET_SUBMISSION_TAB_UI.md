# Budget Submission Tab UI

## Ringkasan

Halaman `resources/views/pages/budget/budget-submission.blade.php` menggunakan pola tab yang sama dengan halaman budget user agar navigasi antar tab lebih rapi dan konsisten.

## UI Pattern

Tab budget submission memakai:

```text
nav nav-tabs nav-tabs-custom mb-3
```

Pola ini mengikuti `resources/views/pages/budget/budget-user.blade.php`:

- underline aktif berwarna oranye
- tab tidak ditempel pada `card-header`
- ikon di setiap tab
- badge pending approval berada di dalam tab `Approval`
- action utama `Add Data` berada di kanan header kecil halaman

## Tab Yang Tersedia

- `Budget Movement`
- `Approval`
- `Approval History`

## File Yang Terlibat

- `resources/views/pages/budget/budget-submission.blade.php`
- `resources/views/pages/budget/budget-user.blade.php` sebagai referensi visual

## Catatan Implementasi

ID tab tetap dipertahankan agar JavaScript existing tidak berubah:

- `#dataTab`
- `#approvalTab`
- `#approvalHistoryTab`

Event listener yang memakai `a[data-bs-toggle="tab"]` tetap berjalan karena atribut Bootstrap tab tidak berubah.
