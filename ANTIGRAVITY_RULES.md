# ANTIGRAVITY_RULES.md - Budget Control Project

> **"Kitab Undang-Undang"** untuk project Budget Control.  
> Setiap kontributor (manusia/AI) **WAJIB** mematuhi rules ini tanpa pengecualian.

---

## 📋 Daftar Isi

1. [Project Identity](#-project-identity)
2. [Tech Stack](#-tech-stack)
3. [Architecture Rules](#-architecture-rules)
4. [Code Conventions](#-code-conventions)
5. [Database Conventions](#-database-conventions)
6. [Frontend Conventions](#-frontend-conventions)
7. [Security Rules](#-security-rules)
8. [Testing Guidelines](#-testing-guidelines)
9. [Git & Deployment](#-git--deployment)

---

## 🎯 Project Identity

| Key                 | Value                                                 |
| ------------------- | ----------------------------------------------------- |
| **Nama**            | Budget Control                                        |
| **Tujuan**          | Sistem manajemen anggaran, KPI, dan approval workflow |
| **Domain**          | Finance / Enterprise Resource Planning                |
| **Bahasa Kode**     | English                                               |
| **Bahasa Komentar** | English preferred, Indonesia allowed                  |

---

## 🛠️ Tech Stack

### Core

-   **Framework**: Laravel 12.x
-   **PHP**: ^8.2
-   **Frontend**: Blade + Livewire 3.x
-   **Database**: MySQL/MariaDB

### Key Packages (JANGAN GANTI tanpa approval)

| Package                               | Versi | Fungsi                 |
| ------------------------------------- | ----- | ---------------------- |
| `spatie/laravel-permission`           | ^6.23 | RBAC                   |
| `yajra/laravel-datatables-oracle`     | ^12.6 | Server-side DataTables |
| `maatwebsite/excel`                   | ^3.1  | Excel import/export    |
| `barryvdh/laravel-dompdf`             | ^3.1  | PDF generation         |
| `livewire/livewire`                   | ^3.6  | Reactive components    |
| `power-components/livewire-powergrid` | ^6.6  | DataGrid               |

---

## 🏗️ Architecture Rules

### Directory Structure

```
app/
├── Exports/          # Maatwebsite export classes
├── Helpers/          # Global helper functions (autoloaded)
├── Http/
│   └── Controllers/  # Satu controller per resource
├── Imports/          # Maatwebsite import classes
├── Jobs/             # Queue jobs
├── Livewire/         # Livewire components
├── Models/           # Eloquent models
├── Providers/        # Service providers (termasuk CustomServiceProvider)
└── Services/         # Business logic layer
    ├── {ServiceName}/
    │   ├── {ServiceName}Service.php     # Interface (kontrak)
    │   └── {ServiceName}ServiceImpl.php # Implementation
    └── ...

resources/views/
├── components/       # Blade components
├── include/          # Partial views (sidebar, header, etc.)
├── layouts/          # Layout templates
├── livewire/         # Livewire component views
└── pages/            # Main page views per module
```

### Pattern yang WAJIB Diikuti

#### 1. Controller Pattern (Orchestrator Only)

> [!IMPORTANT]
> Controller **HANYA** berfungsi sebagai orchestrator. Semua business logic **WAJIB** didelegasikan ke Service Layer.

```php
use App\Services\ExampleService\ExampleService;

class ExampleController extends Controller
{
    public function __construct(
        private readonly ExampleService $exampleService
    ) {}

    // 1. View methods (return view)
    public function index() { }
    public function create() { }
    public function edit($id) { }

    // 2. API/Data methods (return JSON)
    public function getData(Request $request) { }  // untuk DataTables
    public function data() { }                     // alternatif naming

    // 3. CRUD methods - delegate ke service
    public function store(Request $request)
    {
        $validated = $request->validated();
        $result = $this->exampleService->create($validated);
        return response()->json(['success' => true, 'data' => $result]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validated();
        $result = $this->exampleService->update($id, $validated);
        return response()->json(['success' => true, 'data' => $result]);
    }

    public function destroy($id)
    {
        $this->exampleService->delete($id);
        return response()->json(['success' => true]);
    }

    // 4. Action methods - delegate ke service
    public function approve($id)
    {
        $this->exampleService->approve($id);
        return response()->json(['success' => true]);
    }
}
```

---

#### 2. Service Layer Pattern (Business Logic)

> [!IMPORTANT]
> Ini adalah **WAJIB** pattern untuk semua fitur baru. Generate service menggunakan:
>
> ```bash
> php artisan make:service {ServiceName}
> ```

##### Directory Structure

```
app/Services/
├── ExampleService/
│   ├── ExampleService.php         # Interface (kontrak)
│   └── ExampleServiceImpl.php     # Implementation (logika bisnis)
├── ApprovalService/
│   ├── ApprovalService.php
│   └── ApprovalServiceImpl.php
└── ...
```

##### Interface (Kontrak)

```php
<?php

namespace App\Services\ExampleService;

/**
 * Service interface for Example operations.
 *
 * Kontrak ini mendefinisikan semua method yang HARUS diimplementasikan.
 * Controller akan depend pada interface ini, bukan implementasi langsung.
 */
interface ExampleService
{
    /**
     * Create a new resource.
     *
     * @param array $data Validated input data
     * @return mixed Created resource
     */
    public function create(array $data): mixed;

    /**
     * Update an existing resource.
     *
     * @param int $id Resource ID
     * @param array $data Validated input data
     * @return mixed Updated resource
     */
    public function update(int $id, array $data): mixed;

    /**
     * Delete a resource (soft delete preferred).
     *
     * @param int $id Resource ID
     * @return bool Success status
     */
    public function delete(int $id): bool;

    /**
     * Get resource by ID.
     *
     * @param int $id Resource ID
     * @return mixed Resource or null
     */
    public function findById(int $id): mixed;

    /**
     * Get all resources with optional filters.
     *
     * @param array $filters Optional filters
     * @return \Illuminate\Support\Collection
     */
    public function getAll(array $filters = []): \Illuminate\Support\Collection;
}
```

##### Implementation (Logika Bisnis)

```php
<?php

namespace App\Services\ExampleService;

use App\Models\Example;
use App\Services\ExampleService\ExampleService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExampleServiceImpl implements ExampleService
{
    public function __construct(
        private readonly Example $model,
        // Inject dependencies lain di sini jika perlu
    ) {}

    public function create(array $data): mixed
    {
        return DB::transaction(function () use ($data) {
            // Business logic di sini
            return $this->model->create($data);
        });
    }

    public function update(int $id, array $data): mixed
    {
        return DB::transaction(function () use ($id, $data) {
            $resource = $this->findById($id);
            $resource->update($data);
            return $resource->fresh();
        });
    }

    public function delete(int $id): bool
    {
        $resource = $this->findById($id);
        return $resource->delete();
    }

    public function findById(int $id): mixed
    {
        return $this->model->findOrFail($id);
    }

    public function getAll(array $filters = []): Collection
    {
        $query = $this->model->query();

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }
}
```

##### Service Binding (Dependency Injection)

Service binding dilakukan di `CustomServiceProvider`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CustomServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Binding Interface ke Implementation
        $this->app->bind(
            \App\Services\ExampleService\ExampleService::class,
            \App\Services\ExampleService\ExampleServiceImpl::class
        );

        // Tambahkan binding lain di sini
    }

    public function boot()
    {
        //
    }
}
```

##### Service Guidelines

| Rule                                     | Deskripsi                                            |
| ---------------------------------------- | ---------------------------------------------------- |
| **Single Responsibility**                | Satu service = satu domain/aggregate                 |
| **Interface First**                      | Selalu definisikan interface sebelum implementation  |
| **Dependency Injection**                 | Inject via constructor, jangan pakai `app()` helper  |
| **Transaction Safety**                   | Gunakan `DB::transaction()` untuk operasi multi-step |
| **Exception Handling**                   | Throw custom exception untuk error bisnis            |
| **Return Types**                         | Selalu definisikan return type di interface          |
| **No Direct Model Access in Controller** | Controller tidak boleh query model langsung          |

#### 3. Model Pattern

```php
class Example extends Model
{
    use SoftDeletes;  // WAJIB untuk data penting

    protected $fillable = [...];
    protected $casts = [...];

    // 1. Constants untuk status/enum
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;

    // 2. Relationships
    public function parent() { }
    public function children() { }

    // 3. Scopes
    public function scopePending($query) { }
    public function scopeByYear($query, $year) { }

    // 4. Helper methods
    public function isPending() { }
    public function getStatusLabel() { }
    public function getStatusBadgeClass() { }
}
```

#### 4. Response Pattern (untuk AJAX)

```php
// Success
return response()->json([
    'success' => true,
    'message' => 'Operation successful',
    'data' => $data
]);

// Error
return response()->json([
    'success' => false,
    'message' => 'Error message',
    'errors' => $validator->errors()  // jika ada
], 422);
```

---

## 📝 Code Conventions

### Naming Conventions

| Item            | Convention              | Contoh                                   |
| --------------- | ----------------------- | ---------------------------------------- |
| **Model**       | PascalCase, Singular    | `Transaction`, `KPIWorkPlan`             |
| **Controller**  | PascalCase + Controller | `TransactionController`                  |
| **Table**       | snake_case, Plural      | `transactions`, `kpi_workplans`          |
| **Column**      | snake_case              | `created_at`, `user_id`                  |
| **Migration**   | snake_case, descriptive | `create_transactions_table`              |
| **Route name**  | kebab-case dengan dot   | `transaction.store`, `budget.user.index` |
| **View folder** | kebab-case              | `resources/views/pages/work-plan/`       |
| **Variable**    | camelCase               | `$totalAmount`, `$pendingApprovals`      |
| **Constant**    | UPPER_SNAKE_CASE        | `STATUS_PENDING`                         |

### Route Naming Convention

```php
// Pattern: {module}.{action} atau {module}.{sub}.{action}
Route::get('/', [Controller::class, 'index'])->name('transaction.index');
Route::post('/', [Controller::class, 'store'])->name('transaction.store');
Route::get('/data', [Controller::class, 'getData'])->name('transaction.data');
Route::post('/{id}/approve', [Controller::class, 'approve'])->name('transaction.approve');
```

### Comments

```php
// WAJIB: DocBlock untuk method kompleks
/**
 * Calculate approval chain based on amount threshold
 *
 * @param int $transactionId
 * @return array
 */
public function createApprovalChain($transactionId) { }

// Section markers untuk controller besar
/* ========================
    APPROVAL ACTIONS
======================== */
```

---

## 💾 Database Conventions

### Migration Rules

1. **Prefix Timestamp**: Ikuti format Laravel `YYYY_MM_DD_HHMMSS`
2. **Naming**: `{action}_{columns}_to_{table}_table.php`
3. **Reversible**: Selalu implementasi `down()` method
4. **Nullable**: Default NULL untuk optional fields

### Foreign Key Naming

```php
// Pattern: {table}_{column}_foreign
$table->foreign('user_id')
    ->references('id')
    ->on('users')
    ->onDelete('cascade');
```

### Soft Deletes

**WAJIB** untuk tabel: `transactions`, `approvals`, `workplans`, `budget_items`, dan semua tabel yang butuh audit trail.

### Index Naming

```php
// Pattern: {table}_{columns}_index
$table->index(['status', 'created_at'], 'transactions_status_created_index');
```

---

## 🎨 Frontend Conventions

### Blade Views

1. **Layout**: Extend dari `layouts/app.blade.php`
2. **Partials**: `@include('include.component')` untuk reusable parts
3. **Stack**: Gunakan `@push('scripts')` untuk page-specific JS

### DataTables Pattern

```javascript
// Gunakan Yajra DataTables dengan server-side processing
$('#dataTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: '{{ route("module.data") }}',
        data: function(d) {
            d.filter_year = $('#filter_year').val();
        }
    },
    columns: [...]
});
```

### Modal Pattern

```html
<!-- Modal wrapper di setiap page yang butuh -->
<div class="modal fade" id="modalForm" tabindex="-1">
    <!-- Form content -->
</div>

<!-- Trigger -->
<button data-bs-toggle="modal" data-bs-target="#modalForm">Add</button>
```

### Currency Formatting

```javascript
// Gunakan pattern ini untuk format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(amount);
}
```

---

## 🔐 Security Rules

### Role & Permission Management

> [!IMPORTANT]
> Budget Control menggunakan **Spatie Laravel Permission** sebagai **satu-satunya** sistem untuk role & permission management.

#### Best Practices

**✅ GUNAKAN Spatie untuk semua role & permission:**

```php
// Permission checking
if ($employee->can('transaction.create')) { }

// Role checking
if ($employee->hasRole('admin')) { }

// Middleware
Route::middleware('role:admin')->group(function () { });
Route::middleware('permission:transaction.view')->group(function () { });

// Assign role
$employee->assignRole('editor');

// Sync roles (replace all)
$employee->syncRoles(['editor', 'writer']);

// Get role name untuk display
$roleName = $employee->getPrimaryRoleName(); // helper method
// atau
$roleName = $employee->roles->first()?->name ?? 'No Role';
```

### Permission Middleware

```php
// WAJIB pada setiap route yang butuh akses kontrol
Route::prefix('transaction')
    ->middleware('permission:transaction.view')
    ->group(function () {
        // routes here
    });
```

### Permission Naming

Pattern: `{module}.{action}`

```
transaction.view
transaction.create
transaction.edit
transaction.delete
transaction.approve
```

### Input Validation

```php
// WAJIB validasi di controller
$validator = Validator::make($request->all(), [
    'amount' => 'required|numeric|min:0',
    'description' => 'required|string|max:255',
]);

if ($validator->fails()) {
    return response()->json([
        'success' => false,
        'errors' => $validator->errors()
    ], 422);
}
```

### XSS Prevention

-   Blade: Gunakan `{{ }}` bukan `{!! !!}` kecuali terpaksa
-   Jika harus raw HTML: Sanitize dengan `strip_tags()` atau HTMLPurifier

---

## 🧪 Testing Guidelines

### Test Location

```
tests/
├── Feature/    # Integration tests
└── Unit/       # Unit tests untuk services/helpers
```

### Running Tests

```bash
# Semua test
php artisan test

# Specific file
php artisan test tests/Feature/TransactionTest.php

# Dengan filter
php artisan test --filter=ApprovalServiceTest
```

### Test Naming

```php
// Pattern: test_{action}_{condition}
public function test_create_transaction_with_valid_data()
public function test_approval_fails_without_permission()
```

---

## 📦 Git & Deployment

### Commit Message Format

```
{type}: {description}

Types:
- feat: Fitur baru
- fix: Bug fix
- refactor: Refactoring tanpa ubah behaviour
- docs: Dokumentasi
- style: Formatting, missing semicolons, etc.
- test: Adding/fixing tests
- chore: Maintenance tasks

Contoh:
feat: add dynamic approval threshold configuration
fix: correct budget calculation for Q4
```

### Branch Naming

```
feature/{issue-number}-{short-description}
bugfix/{issue-number}-{short-description}
hotfix/{short-description}

Contoh:
feature/123-approval-delegation
bugfix/456-incorrect-budget-total
```

### Pre-Deployment Checklist

-   [ ] `composer install --no-dev`
-   [ ] `php artisan config:cache`
-   [ ] `php artisan route:cache`
-   [ ] `php artisan view:cache`
-   [ ] `php artisan migrate --force`

---

## ⚠️ LARANGAN (Hard Rules)

> [!CAUTION]
> Pelanggaran rules berikut = **Reject PR tanpa review**

1. ❌ **JANGAN** commit langsung ke `main` branch
2. ❌ **JANGAN** hapus migration yang sudah di-production
3. ❌ **JANGAN** store password/secret di code (gunakan `.env`)
4. ❌ **JANGAN** bypass permission middleware
5. ❌ **JANGAN** gunakan `dd()` atau `var_dump()` di production code
6. ❌ **JANGAN** hardcode ID (user_id, role_id, etc.)
7. ❌ **JANGAN** gunakan raw SQL tanpa binding untuk user input
8. ❌ **JANGAN** hapus SoftDeletes dari model yang sudah ada

---

## 📌 Quick Reference

### Common Commands

```bash
# Development
composer dev                    # Start all services (server, queue, logs, vite)
php artisan serve               # Start server only
npm run dev                     # Start Vite

# Database
php artisan migrate             # Run migrations
php artisan migrate:fresh --seed # Reset and reseed DB
php artisan db:seed --class=SeederName

# Cache
php artisan optimize:clear      # Clear all caches

# Testing
php artisan test                # Run all tests
```

### Useful Artisan

```bash
php artisan make:controller NameController
php artisan make:model Name -m       # with migration
php artisan make:service ServiceName # Service interface + implementation + binding
php artisan make:livewire Name
php artisan make:export NameExport
php artisan permission:create-role admin
```

---

**Last Updated**: 2026-01-06  
**Maintained By**: Development Team
