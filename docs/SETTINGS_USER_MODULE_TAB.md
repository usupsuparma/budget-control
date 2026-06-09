# Settings User Module Tab

## Overview

Halaman `Settings > Users & Roles` sekarang memiliki tab baru `Modul` untuk mengelola master data `modul_menu`. Data ini dipakai untuk mengelompokkan permission ke kombinasi modul dan menu yang konsisten.

## User Flow

1. Buka halaman `users.index`.
2. Pindah ke tab `Modul`.
3. Gunakan tombol `Add Modul` untuk menambah data baru.
4. Gunakan tombol edit untuk memperbarui `modul_name` dan `menu_name`.
5. Gunakan tombol delete untuk menghapus modul yang belum dipakai permission.

## Business Rules

- `modul_name` wajib diisi.
- `menu_name` opsional dan akan disimpan `null` bila dikosongkan.
- Kombinasi `modul_name` + `menu_name` tidak boleh duplikat.
- Modul yang masih dipakai oleh tabel `permissions.modul_menu` tidak dapat dihapus.
- Delete menggunakan soft delete pada tabel `modul_menu`.

## Routes

- `POST users/modules` → `users.modules.store`
- `PUT users/modules/{id}` → `users.modules.update`
- `DELETE users/modules/{id}` → `users.modules.destroy`

## Implementation Notes

### Backend

- Page data untuk `Users & Roles` dipusatkan di `UserSettingsService`.
- CRUD modul ditangani oleh:
  - `App\Http\Controllers\ModulMenuController`
  - `App\Services\UserSettingsService\UserSettingsService`
  - `App\Services\UserSettingsService\UserSettingsServiceImpl`
- Validasi request memakai:
  - `StoreModulMenuRequest`
  - `UpdateModulMenuRequest`
- DTO input memakai `UserSettingsService\DTOs\ModulMenuData`.

### Frontend

- Tab baru ditambahkan di `resources/views/pages/settings/users.blade.php`.
- UI CRUD ada di partial `resources/views/authorization/modules.blade.php`.
- Feedback form memakai `SweetAlert2`.

## Touched Files

- `app/Http/Controllers/UsersController.php`
- `app/Http/Controllers/ModulMenuController.php`
- `app/Http/Requests/StoreModulMenuRequest.php`
- `app/Http/Requests/UpdateModulMenuRequest.php`
- `app/Models/ModulMenu.php`
- `app/Providers/CustomServiceProvider.php`
- `app/Services/UserSettingsService/*`
- `resources/views/pages/settings/users.blade.php`
- `resources/views/authorization/modules.blade.php`
- `routes/web.php`

## Testing

- Automated service coverage added in `tests/Feature/Services/UserSettingsServiceTest.php`.
