# UserRoleService — Role-Based Access Control Helper

## Problem Statement

Sebelum service ini dibuat, setiap class yang perlu membedakan akses **admin vs non-admin** menulis logic pengecekan role secara inline dan berbeda-beda:

```php
// KPIDivisionServiceImpl — tidak konsisten
$isAdmin = $user->hasRole('Admin') || $user->hasRole('admin') || $user->hasRole('super-admin');

// KPIDepartmentServiceImpl — berbeda lagi
$user->hasAnyRole(['Admin', 'admin', 'super-admin', 'Super Admin']);
```

Akibatnya, ketika nama role berubah di database (misal `admin` → `Super Admin`), perubahan harus dilakukan di banyak file secara manual dan rentan terlewat.

---

## Solusi: Single Source of Truth

`UserRoleService` adalah satu-satunya tempat di seluruh codebase yang mendefinisikan mana role yang termasuk "admin" (akses penuh) dan mana role yang termasuk "scoped" (akses terbatas per divisi).

---

## File Structure

```
app/Services/UserRoleService/
├── UserRoleService.php       # Interface (contract)
└── UserRoleServiceImpl.php   # Implementation
```

### Binding

Terdaftar di `app/Providers/CustomServiceProvider.php`:

```php
$this->app->bind(
    \App\Services\UserRoleService\UserRoleService::class,
    \App\Services\UserRoleService\UserRoleServiceImpl::class
);
```

---

## Role Registry

Dikelola melalui dua konstanta di `UserRoleServiceImpl`:

```php
// Roles yang mendapatkan akses penuh (tampilkan semua data)
public const ADMIN_ROLES = [
    'Super Admin',
];

// Roles dengan akses terbatas (filter berdasarkan divisi user)
public const SCOPED_ROLES = [
    'Director',
    'Division',
    'Department',
    'Section',
    'User',
];
```

> **Jika nama role berubah di database, cukup update `ADMIN_ROLES` atau `SCOPED_ROLES` di sini. Tidak perlu menyentuh file lain.**

---

## API / Contract

### `isAdmin(mixed $user): bool`

Mengembalikan `true` jika user memiliki salah satu role dalam `ADMIN_ROLES`.

- `null` user → selalu `false`
- Admin → tampilkan **semua** data tanpa filter divisi

### `getDivisionIds(mixed $user): array`

Mengembalikan array of `division.id` yang boleh dilihat user.

| Kondisi | Return |
|---|---|
| User adalah admin | `[]` *(caller harus skip filter → tampilkan semua)* |
| User bukan admin, ada employment | Hasil `Employment::getDivisionIds()` |
| User bukan admin, tidak ada employment | `[]` |

Logika resolusi divisi berdasarkan `job_level_id` (lihat [Employee Org Resolution](EMPLOYEE_ORG_RESOLUTION.md)):

| Level | Struktur | Divisi yang dikembalikan |
|---|---|---|
| 1 — Director | `director.id` | Semua divisi di bawah director tersebut |
| 2 — Division | `division.id` | Divisi itu sendiri |
| 3 — Department | `department.id` | Divisi parent dari department |
| 4+ — Section/Staff | `section.id` | Divisi parent dari section → department |

### `getPrimaryRole(mixed $user): ?string`

Mengembalikan nama role pertama milik user, atau `null` jika tidak ada.

---

## Cara Penggunaan

### Inject melalui Constructor (Services & Controllers)

```php
use App\Services\UserRoleService\UserRoleService;

class MyServiceImpl implements MyService
{
    public function __construct(private UserRoleService $userRoleService) {}

    public function getIndexData(): array
    {
        $user = Auth::user();
        $isAdmin = $this->userRoleService->isAdmin($user);

        $query = MyModel::query();

        if (! $isAdmin) {
            $divisionIds = $this->userRoleService->getDivisionIds($user);
            $query->whereIn('division_id', $divisionIds);
        }

        return $query->get()->toArray();
    }
}
```

### Pattern Admin / Scoped yang Benar

```php
$isAdmin = $this->userRoleService->isAdmin($user);

if ($isAdmin) {
    // Tampilkan semua data
    $data = Model::all();
} else {
    // Filter berdasarkan divisi user
    $divisionIds = $this->userRoleService->getDivisionIds($user);
    $data = Model::whereIn('division_id', $divisionIds)->get();
}
```

---

## Modules yang Sudah Direfactor

| File | Sebelum | Sesudah |
|---|---|---|
| `KPIDivisionServiceImpl` | `hasRole('Admin') \|\| hasRole('admin') \|\| hasRole('super-admin')` | `$userRoleService->isAdmin($user)` |
| `KPIDepartmentServiceImpl` | Private `isAdmin()` + `getDivisionIds()` lokal | Inject `UserRoleService` |
| `KPIWorkPlanController` | Inline switch-case + `hasRole` | `$userRoleService->isAdmin()` + `getDivisionIds()` |

---

## Cara Menambah Role Admin Baru

1. Buka `app/Services/UserRoleService/UserRoleServiceImpl.php`
2. Tambahkan nama role ke konstanta `ADMIN_ROLES`:

```php
public const ADMIN_ROLES = [
    'Super Admin',
    'Finance Manager', // tambah di sini
];
```

3. Tidak ada perubahan lain yang diperlukan di seluruh codebase.

---

## Cara Menambah Role Scoped Baru

1. Tambahkan ke database via Seeder atau migration.
2. Tambahkan nama role ke `SCOPED_ROLES` (untuk dokumentasi & future use):

```php
public const SCOPED_ROLES = [
    'Director',
    'Division',
    'Department',
    'Section',
    'User',
    'Auditor', // tambah di sini
];
```

3. Pastikan user dengan role baru memiliki `Employment` yang valid agar `getDivisionIds()` bisa resolve divisinya.

---

## Testing Mandate

Setiap perubahan pada `UserRoleServiceImpl` **harus disertai** test di `tests/Unit/Services/UserRoleServiceTest.php` yang mencakup:

- `isAdmin()` dengan user admin → `true`
- `isAdmin()` dengan user non-admin → `false`
- `isAdmin()` dengan `null` → `false`
- `getDivisionIds()` dengan admin → `[]`
- `getDivisionIds()` dengan non-admin yang memiliki employment → array divisi
- `getDivisionIds()` dengan non-admin tanpa employment → `[]`
