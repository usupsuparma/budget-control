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

- **Framework**: Laravel 12.x
- **PHP**: ^8.2
- **Frontend**: Blade + Livewire 3.x
- **Database**: MySQL/MariaDB

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
│   └── Controllers/  # Satu controller per resource (orchestrator ONLY)
├── Imports/          # Maatwebsite import classes
├── Jobs/             # Queue jobs
├── Livewire/         # Livewire components
├── Models/           # Eloquent models
├── Providers/        # Service providers (termasuk CustomServiceProvider)
└── Services/         # Business logic layer — SEMUA logika bisnis ada di sini
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

---

### ⚡ Prinsip Utama: Controller = Orchestrator, Service = Executor

> [!IMPORTANT]
> **Controller TIDAK BOLEH mengandung business logic apapun.**  
> Controller hanya boleh:
>
> 1. Menerima HTTP request
> 2. Memvalidasi input
> 3. Memanggil service
> 4. Mengembalikan response
>
> **Semua logika** — termasuk operasi CRUD, kalkulasi, kondisi bisnis, dan query kompleks — **WAJIB** berada di Service Layer.

---

### Pattern yang WAJIB Diikuti

#### 1. Controller Pattern (Orchestrator Only)

Controller hanya bertanggung jawab atas alur HTTP. Tidak ada logika bisnis, tidak ada query model langsung.

##### ✅ BENAR — Controller sebagai Orchestrator

```php
use App\Services\ExampleService\ExampleService;

class ExampleController extends Controller
{
    public function __construct(
        private readonly ExampleService $exampleService
    ) {}

    /* ========================
        VIEW METHODS
    ======================== */

    public function index()
    {
        return view('pages.example.index');
    }

    public function create()
    {
        return view('pages.example.create');
    }

    public function edit($id)
    {
        $data = $this->exampleService->findById($id);
        return view('pages.example.edit', compact('data'));
    }

    /* ========================
        DATA / DATATABLES
    ======================== */

    public function getData(Request $request)
    {
        return $this->exampleService->getDataTable($request);
    }

    /* ========================
        CRUD ACTIONS
    ======================== */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        // Delegasikan ke service — controller tidak tahu cara membuat record
        $result = $this->exampleService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan.',
            'data'    => $result,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        // Delegasikan ke service
        $result = $this->exampleService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui.',
            'data'    => $result,
        ]);
    }

    public function destroy(int $id)
    {
        // Delegasikan ke service
        $this->exampleService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus.',
        ]);
    }

    /* ========================
        APPROVAL ACTIONS
    ======================== */

    public function approve(int $id)
    {
        $this->exampleService->approve($id);

        return response()->json(['success' => true, 'message' => 'Disetujui.']);
    }

    public function reject(Request $request, int $id)
    {
        $validated = $request->validate(['reason' => 'required|string']);

        $this->exampleService->reject($id, $validated['reason']);

        return response()->json(['success' => true, 'message' => 'Ditolak.']);
    }
}
```

##### ❌ SALAH — Controller mengandung business logic (DILARANG KERAS)

```php
// ❌ JANGAN LAKUKAN INI — violates orchestrator pattern
public function store(Request $request)
{
    // ❌ Query model langsung di controller
    $exists = Example::where('name', $request->name)->exists();
    if ($exists) {
        return response()->json(['success' => false, 'message' => 'Sudah ada.'], 422);
    }

    // ❌ Business logic (kalkulasi, kondisi) di controller
    $amount = $request->amount;
    if ($amount > 1000000) {
        $request->merge(['requires_approval' => true]);
    }

    // ❌ Operasi DB langsung di controller
    $data = Example::create($request->all());

    // ❌ Multiple model operations tanpa service
    $data->histories()->create(['action' => 'created', 'user_id' => auth()->id()]);

    return response()->json(['success' => true, 'data' => $data]);
}
```

> **Aturan sederhana:** Jika kamu menulis `Model::`, `DB::`, `if (bisnis condition)`, atau kalkulasi apapun di controller — **pindahkan ke service.**

---

#### 2. Service Layer Pattern (Business Logic)

> [!IMPORTANT]
> Semua CRUD, kalkulasi, kondisi bisnis, query kompleks, dan operasi multi-step **WAJIB** diimplementasikan di sini.
>
> Generate service menggunakan:
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

Setiap service **WAJIB** memiliki interface. Controller depend pada interface, bukan concrete implementation.

```php
<?php

namespace App\Services\ExampleService;

use Illuminate\Support\Collection;
use Illuminate\Http\Request;

/**
 * Service interface for Example operations.
 *
 * Kontrak ini mendefinisikan semua method CRUD dan business operations.
 * Controller akan depend pada interface ini, bukan implementasi langsung.
 */
interface ExampleService
{
    /**
     * Get all resources dengan optional filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Get resource by ID. Throw exception jika tidak ditemukan.
     *
     * @param int $id
     * @return mixed
     */
    public function findById(int $id): mixed;

    /**
     * Create a new resource.
     * Business rules (validasi duplikat, auto-assignment, dll) ditangani di sini.
     *
     * @param array $data Validated input dari controller
     * @return mixed Created resource
     */
    public function create(array $data): mixed;

    /**
     * Update an existing resource.
     *
     * @param int   $id
     * @param array $data Validated input dari controller
     * @return mixed Updated resource
     */
    public function update(int $id, array $data): mixed;

    /**
     * Soft delete a resource.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Provide DataTable response untuk Yajra.
     *
     * @param Request $request
     * @return mixed DataTable response
     */
    public function getDataTable(Request $request): mixed;

    /**
     * Approve a resource (ubah status + trigger side effects).
     *
     * @param int $id
     * @return mixed
     */
    public function approve(int $id): mixed;

    /**
     * Reject a resource dengan alasan.
     *
     * @param int    $id
     * @param string $reason
     * @return mixed
     */
    public function reject(int $id, string $reason): mixed;
}
```

##### Implementation (Logika Bisnis)

Semua business logic, query, dan operasi multi-step ada di sini.

```php
<?php

namespace App\Services\ExampleService;

use App\Models\Example;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ExampleServiceImpl implements ExampleService
{
    public function __construct(
        private readonly Example $model,
        // Inject dependency lain jika perlu (e.g., NotificationService)
    ) {}

    public function getAll(array $filters = []): Collection
    {
        $query = $this->model->query()->with(['relasi1', 'relasi2']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('created_at', $filters['year']);
        }

        return $query->get();
    }

    public function findById(int $id): mixed
    {
        return $this->model->with(['relasi1', 'relasi2'])->findOrFail($id);
    }

    public function create(array $data): mixed
    {
        return DB::transaction(function () use ($data) {
            // Business rule: set default values jika perlu
            $data['status']     = Example::STATUS_PENDING;
            $data['created_by'] = Auth::id();

            // Business rule: tentukan apakah butuh approval
            if ($data['amount'] > 1_000_000) {
                $data['requires_approval'] = true;
            }

            $resource = $this->model->create($data);

            // Side effect: simpan history
            $resource->histories()->create([
                'action'  => 'created',
                'user_id' => Auth::id(),
            ]);

            return $resource->load('relasi1');
        });
    }

    public function update(int $id, array $data): mixed
    {
        return DB::transaction(function () use ($id, $data) {
            $resource = $this->findById($id);

            // Business rule: jika amount berubah, reset approval status
            if (isset($data['amount']) && $data['amount'] !== $resource->amount) {
                $data['status'] = Example::STATUS_PENDING;
            }

            $resource->update($data);

            $resource->histories()->create([
                'action'  => 'updated',
                'user_id' => Auth::id(),
            ]);

            return $resource->fresh('relasi1');
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $resource = $this->findById($id);

            // Business rule: tidak bisa hapus jika sudah approved
            if ($resource->status === Example::STATUS_APPROVED) {
                throw new \Exception('Data yang sudah disetujui tidak dapat dihapus.');
            }

            $resource->histories()->create([
                'action'  => 'deleted',
                'user_id' => Auth::id(),
            ]);

            return $resource->delete(); // SoftDelete
        });
    }

    public function getDataTable(Request $request): mixed
    {
        $query = $this->model->with('relasi1')
            ->select('examples.*');

        // Apply filter dari request
        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }

        return DataTables::of($query)
            ->addColumn('status_badge', fn($row) => $row->getStatusBadgeClass())
            ->addColumn('action', fn($row) => view('pages.example._action', compact('row'))->render())
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function approve(int $id): mixed
    {
        return DB::transaction(function () use ($id) {
            $resource = $this->findById($id);

            $resource->update([
                'status'      => Example::STATUS_APPROVED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Trigger notification, event, dll
            // event(new ExampleApproved($resource));

            return $resource->fresh();
        });
    }

    public function reject(int $id, string $reason): mixed
    {
        return DB::transaction(function () use ($id, $reason) {
            $resource = $this->findById($id);

            $resource->update([
                'status'      => Example::STATUS_REJECTED,
                'rejected_by' => Auth::id(),
                'rejected_at' => now(),
                'reject_reason' => $reason,
            ]);

            return $resource->fresh();
        });
    }
}
```

##### Service Binding (Dependency Injection)

Service binding dilakukan di `CustomServiceProvider`. **Setiap service baru WAJIB didaftarkan di sini.**

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CustomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \App\Services\ExampleService\ExampleService::class,
            \App\Services\ExampleService\ExampleServiceImpl::class
        );

        $this->app->bind(
            \App\Services\ApprovalService\ApprovalService::class,
            \App\Services\ApprovalService\ApprovalServiceImpl::class
        );

        // Tambahkan binding lain di sini
    }

    public function boot(): void {}
}
```

##### Service Guidelines

| Rule                                     | Deskripsi                                                             |
| ---------------------------------------- | --------------------------------------------------------------------- |
| **Single Responsibility**                | Satu service = satu domain/aggregate                                  |
| **Interface First**                      | Selalu definisikan interface sebelum implementation                   |
| **All CRUD in Service**                  | create/read/update/delete **HANYA** di service, bukan controller      |
| **All Business Logic in Service**        | Kalkulasi, kondisi, validasi bisnis **HANYA** di service              |
| **Dependency Injection**                 | Inject via constructor, jangan pakai `app()` helper                   |
| **Transaction Safety**                   | Gunakan `DB::transaction()` untuk semua operasi multi-step            |
| **Exception Handling**                   | Throw exception untuk error bisnis, tangkap di controller jika perlu  |
| **Return Types**                         | Selalu definisikan return type di interface                           |
| **No Direct Model Access in Controller** | Controller **tidak boleh** memanggil `Model::` apapun secara langsung |
| **No DB Queries in Controller**          | Controller **tidak boleh** menggunakan `DB::` secara langsung         |

---

#### 3. Model Pattern

```php
class Example extends Model
{
    use SoftDeletes;  // WAJIB untuk data penting

    protected $fillable = [...];
    protected $casts    = [...];

    // 1. Constants untuk status/enum
    const STATUS_PENDING  = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    // 2. Relationships
    public function parent()   { return $this->belongsTo(Parent::class); }
    public function children() { return $this->hasMany(Child::class); }
    public function histories(){ return $this->morphMany(History::class, 'historyable'); }

    // 3. Scopes
    public function scopePending($query) { return $query->where('status', self::STATUS_PENDING); }
    public function scopeByYear($query, $year) { return $query->whereYear('created_at', $year); }

    // 4. Helper methods (boleh di model, bukan business logic)
    public function isPending(): bool   { return $this->status === self::STATUS_PENDING; }
    public function isApproved(): bool  { return $this->status === self::STATUS_APPROVED; }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING  => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            default               => 'Unknown',
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_APPROVED => '<span class="badge bg-success">Approved</span>',
            self::STATUS_REJECTED => '<span class="badge bg-danger">Rejected</span>',
            default               => '<span class="badge bg-warning">Pending</span>',
        };
    }
}
```

#### 4. Response Pattern (untuk AJAX)

```php
// Success
return response()->json([
    'success' => true,
    'message' => 'Operation successful',
    'data'    => $data,
]);

// Error validasi
return response()->json([
    'success' => false,
    'message' => 'Validasi gagal.',
    'errors'  => $validator->errors(),
], 422);

// Error bisnis (dari service exception)
return response()->json([
    'success' => false,
    'message' => $e->getMessage(),
], 422);
```

---

## 📝 Code Conventions

### Naming Conventions

| Item            | Convention              | Contoh                                   |
| --------------- | ----------------------- | ---------------------------------------- |
| **Model**       | PascalCase, Singular    | `Transaction`, `KPIWorkPlan`             |
| **Controller**  | PascalCase + Controller | `TransactionController`                  |
| **Service**     | PascalCase + Service    | `TransactionService`, `ApprovalService`  |
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
Route::get('/',          [Controller::class, 'index'])->name('transaction.index');
Route::get('/create',    [Controller::class, 'create'])->name('transaction.create');
Route::post('/',         [Controller::class, 'store'])->name('transaction.store');
Route::get('/{id}/edit', [Controller::class, 'edit'])->name('transaction.edit');
Route::put('/{id}',      [Controller::class, 'update'])->name('transaction.update');
Route::delete('/{id}',   [Controller::class, 'destroy'])->name('transaction.destroy');
Route::get('/data',      [Controller::class, 'getData'])->name('transaction.data');
Route::post('/{id}/approve', [Controller::class, 'approve'])->name('transaction.approve');
Route::post('/{id}/reject',  [Controller::class, 'reject'])->name('transaction.reject');
```

### Comments

```php
// WAJIB: DocBlock untuk method kompleks di service
/**
 * Calculate approval chain based on amount threshold.
 * Setiap threshold memiliki approver yang berbeda (lihat tabel budget_thresholds).
 *
 * @param int $transactionId
 * @return array Ordered list of approver user IDs
 */
public function buildApprovalChain(int $transactionId): array { }

// Section markers untuk service/controller besar
/* ========================
    APPROVAL OPERATIONS
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
            d.filter_year   = $('#filter_year').val();
            d.filter_status = $('#filter_status').val();
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

```php
// Permission checking
if ($employee->can('transaction.create')) { }

// Role checking
if ($employee->hasRole('admin')) { }

// Middleware pada route
Route::middleware('role:admin')->group(function () { });
Route::middleware('permission:transaction.view')->group(function () { });

// Assign / sync role
$employee->assignRole('editor');
$employee->syncRoles(['editor', 'writer']);
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

### Permission Naming — Pattern: `{module}.{action}`

```
transaction.view
transaction.create
transaction.edit
transaction.delete
transaction.approve
```

### Input Validation

Validasi **WAJIB** dilakukan di controller menggunakan `$request->validate()` sebelum data dikirim ke service.

```php
$validated = $request->validate([
    'amount'      => 'required|numeric|min:0',
    'description' => 'required|string|max:255',
]);

// Kirim $validated ke service — BUKAN $request->all()
$result = $this->exampleService->create($validated);
```

### XSS Prevention

- Blade: Gunakan `{{ }}` bukan `{!! !!}` kecuali terpaksa
- Jika harus raw HTML: Sanitize dengan `strip_tags()` atau HTMLPurifier

---

## 🧪 Testing Guidelines

### Test Location

```
tests/
├── Feature/    # Integration tests (Controller → Service → DB)
└── Unit/       # Unit tests untuk service methods secara terisolasi
```

### Running Tests

```bash
php artisan test
php artisan test tests/Unit/ExampleServiceTest.php
php artisan test --filter=ExampleServiceTest
```

### Test Naming

```php
// Pattern: test_{action}_{condition}
public function test_create_sets_pending_status_by_default()
public function test_create_sets_requires_approval_when_amount_exceeds_threshold()
public function test_delete_throws_exception_when_already_approved()
public function test_approve_updates_status_and_records_approver()
```

> **Catatan:** Karena business logic ada di service, unit test **WAJIB** menguji service secara langsung — bukan melalui HTTP.

---

## 📦 Git & Deployment

### Commit Message Format

```
{type}: {description}

Types:
- feat:     Fitur baru
- fix:      Bug fix
- refactor: Refactoring tanpa ubah behaviour
- docs:     Dokumentasi
- style:    Formatting, missing semicolons, etc.
- test:     Adding/fixing tests
- chore:    Maintenance tasks

Contoh:
feat: add dynamic approval threshold configuration
fix: correct budget calculation for Q4
refactor: move approval logic from controller to ApprovalService
```

### Branch Naming

```
feature/{issue-number}-{short-description}
bugfix/{issue-number}-{short-description}
hotfix/{short-description}
```

### Pre-Deployment Checklist

- [ ] `composer install --no-dev`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `php artisan migrate --force`

---

## ⚠️ LARANGAN (Hard Rules)

> [!CAUTION]
> Pelanggaran rules berikut = **Reject PR tanpa review**

### Umum

1. ❌ **JANGAN** commit langsung ke `main` branch
2. ❌ **JANGAN** hapus migration yang sudah di-production
3. ❌ **JANGAN** store password/secret di code (gunakan `.env`)
4. ❌ **JANGAN** bypass permission middleware
5. ❌ **JANGAN** gunakan `dd()` atau `var_dump()` di production code
6. ❌ **JANGAN** hardcode ID (user_id, role_id, etc.)
7. ❌ **JANGAN** gunakan raw SQL tanpa binding untuk user input
8. ❌ **JANGAN** hapus SoftDeletes dari model yang sudah ada

### Architecture (CRUD & Service Layer)

9. ❌ **JANGAN** menulis query `Model::` apapun di dalam controller
10. ❌ **JANGAN** menulis `DB::` apapun di dalam controller
11. ❌ **JANGAN** menulis kondisi bisnis (`if amount > X`, `if status == Y`) di controller
12. ❌ **JANGAN** melakukan operasi multi-step tanpa `DB::transaction()` di service
13. ❌ **JANGAN** membuat service tanpa interface-nya
14. ❌ **JANGAN** inject `ServiceImpl` langsung ke controller — selalu inject Interface
15. ❌ **JANGAN** lupa mendaftarkan service binding baru di `CustomServiceProvider`
16. ❌ **JANGAN** menaruh logika DataTables di controller — delegate ke service method

---

## 📌 Quick Reference

### Common Commands

```bash
# Development
composer dev                     # Start all services (server, queue, logs, vite)
php artisan serve                # Start server only
npm run dev                      # Start Vite

# Database
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed --class=SeederName

# Cache
php artisan optimize:clear

# Testing
php artisan test
```

### Useful Artisan

```bash
php artisan make:controller NameController
php artisan make:model Name -m           # with migration
php artisan make:service ServiceName     # Service interface + implementation + binding
php artisan make:livewire Name
php artisan make:export NameExport
php artisan permission:create-role admin
```

### Checklist Membuat Fitur Baru

```
[ ] Buat migration + model  (php artisan make:model Name -m)
[ ] Buat service             (php artisan make:service NameService)
[ ] Implementasikan CRUD di  NameServiceImpl.php
[ ] Daftarkan binding di     CustomServiceProvider
[ ] Buat controller          (inject NameService interface saja)
[ ] Controller hanya         validate → call service → return response
[ ] Tambahkan routes dengan  permission middleware
[ ] Buat views               (extend layouts/app.blade.php)
[ ] Tulis unit test untuk    NameServiceImpl
```

---

**Last Updated**: 2026-02-18  
**Maintained By**: Development Team
