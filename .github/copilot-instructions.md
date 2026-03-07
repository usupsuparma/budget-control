# Budget Control AI Coding Agent Instructions

## Project Overview

Budget Control is a Laravel 12 enterprise application for budget management, KPI tracking, and approval workflows with two-phase dynamic approval system (uppline chain → master flow with threshold-based routing).

## Critical Architecture Patterns

### Service Layer Pattern (MANDATORY)

All business logic MUST use the Interface + Implementation pattern. Controllers are orchestrators only.

**Directory structure:**

```
app/Services/{ServiceName}/
├── {ServiceName}Service.php       # Interface (contract)
└── {ServiceName}ServiceImpl.php   # Implementation (logic)
```

**Generate new services:**

```bash
php artisan make:service ServiceName
```

**Binding in `app/Providers/CustomServiceProvider.php`:**

```php
$this->app->bind(
    \App\Services\ExampleService\ExampleService::class,
    \App\Services\ExampleService\ExampleServiceImpl::class
);
```

**Controller pattern:**

- NO direct model queries in controllers
- ALL business logic delegates to injected service interfaces
- Return JSON for AJAX: `['success' => bool, 'message' => string, 'data' => mixed]`

### Approval System Architecture

Two-phase sequential approval with immutable snapshots:

1. **Phase 1: Uppline Chain** - Follows `users.uppline_id` recursively until NULL
2. **Phase 2: Master Flow** - Threshold-based (`amount <= threshold`) or all-levels mode

**Key tables:**

- `approval_modules` - Registered modules (transactions, budget, lpj)
- `approval_flow_templates` - Flow rules per module
- `approval_flow_details` - Level configurations (threshold, sequence)
- `approval_requests` - Active approval instances (immutable snapshot)
- `approval_request_details` - Per-level approval status

**Rules:**

- Template changes don't affect in-flight approvals (snapshot at creation)
- Sequential approval: Level N must approve before Level N+1
- Threshold logic: Skip levels where `amount > threshold` of next level

See [documentasi/APPROVAL_SYSTEM.md](../documentasi/APPROVAL_SYSTEM.md) for flow diagrams.

### Import/Export Pattern

Uses `maatwebsite/excel` with dedicated classes in `app/Imports/` and `app/Exports/`:

- Imports: Implement `ToModel`, `WithHeadingRow`, `WithValidation`
- Exports: Use `FromQuery` (preferred for large data — prevents OOM) or `FromCollection` for small sets
- Implement `WithHeadings` and `WithMapping` on all exports
- Queue large operations: `->queue()` method
- Multi-sheet exports: Implement `WithMultipleSheets` with dedicated Sheet classes in `app/Exports/Sheets/`
- See: `MarketingPlanImport.php`, `SubmissionTemplateExport.php`

**Preferred export pattern (large datasets):**

```php
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class TransactionsExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function query()
    {
        // Returns query builder (NOT ->get() or ::all()) to prevent memory exhaustion
        return Transaction::query()->with('user', 'unit');
    }

    public function headings(): array
    {
        return ['ID', 'Date', 'User', 'Unit', 'Amount', 'Status'];
    }
}
```

### Livewire Components

Uses Livewire 3 with PowerGrid for data tables:

- Force Bootstrap 5 in `boot()`: `config(['livewire-powergrid.framework' => 'bootstrap5'])`
- Example: `app/Livewire/EmployeeTable.php`
- Views in `resources/views/livewire/`

### DataTables Standard

All server-side data tables use `yajra/laravel-datatables-oracle`. The DataTable query MUST use eager loading to prevent N+1 queries.

**Controller method pattern:**

```php
public function getData(Request $request)
{
    $query = Transaction::with(['user', 'unit', 'approvalRequest'])
        ->select('transactions.*');

    return DataTables::of($query)
        ->addColumn('action', function ($row) {
            return '
                <button class="btn btn-sm btn-outline-primary edit-btn" data-id="' . $row->id . '">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger delete-btn" data-id="' . $row->id . '">
                    <i class="bi bi-trash"></i>
                </button>
            ';
        })
        ->rawColumns(['action', 'status_badge'])
        ->make(true);
}
```

**Rules:**

- Always use `->with([...])` on DataTable queries to avoid N+1 queries
- Use `->select('table.*')` when joining to avoid column name collisions
- `rawColumns()` must list every column that outputs HTML
- DataTable endpoint route: `Route::get('/data', ...)->name('module.data')`

### Logging Pattern

All significant actions and processes within services MUST be logged using the `LogService`. This provides essential traceability and debugging capabilities.

**Usage:**
Inject `LogService` into your class constructor:

```php
use App\Services\LogService\LogService;

class YourService
{
    protected $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    public function someMethod($data)
    {
        // Log an informational message
        $this->logService->create(
            'Processing some data in someMethod.',
            [
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'data_id' => $data->id ?? null, // Example: Log a relevant ID
                'user_id' => auth()->id(),      // Example: Log the authenticated user ID
            ],
            'info'
        );

        // ... business logic ...

        // Log a warning
        if (empty($data)) {
            $this->logService->create(
                'Attempted to process empty data.',
                [
                    'class' => __CLASS__,
                    'function' => __FUNCTION__,
                    'input' => request()->all() // Example: Log problematic input
                ],
                'warning'
            );
        }
    }
}
```

**Guidelines:**

- **Exclude Credentials:** NEVER log sensitive information such as passwords, API keys, tokens, or any other credentials. Filter out sensitive data from context arrays.
- **Relevant Message:** Ensure the `message` string clearly describes the event being logged.
- **Standard Context:** Always include `'class' => __CLASS__` and `'function' => __FUNCTION__` in the `$context` array to easily trace the origin of the log entry. Add other relevant data to the context, such as IDs, user information, or input parameters, but **never sensitive data**.
- **Appropriate Level:** Use the correct log level (`info`, `warning`, `error`, `debug`, `notice`, `critical`, `alert`, `emergency`) to reflect the severity of the event.

### Controller as Orchestrator (MANDATORY — CRITICAL)

Controllers MUST NOT contain any business logic. A controller's only job is: **receive request → validate → call service → return response**.

**Layer responsibility:**

| Layer          | Responsibility                                                                   |
| -------------- | -------------------------------------------------------------------------------- |
| **Controller** | Receive request, input validation, call service method(s), return JSON/view      |
| **Service**    | All business logic, rules, calculations, DB transactions, multi-model operations |
| **Model**      | Schema definition, relationships, scopes, attribute helpers                      |

**✅ Correct controller pattern:**

```php
public function store(Request $request)
{
    // 1. Validate input only
    $validated = $request->validate([
        'name'   => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
    ]);

    // 2. Delegate ALL logic to service
    try {
        $result = $this->submissionService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data created successfully.',
            'data'    => $result,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'data'    => null,
        ], 500);
    }
}
```

**❌ Forbidden patterns (auto-reject):**

```php
// ❌ WRONG — Direct model query in controller
$transaction = Transaction::where('user_id', auth()->id())->get();

// ❌ WRONG — Business rule / condition in controller
if ($request->amount > $user->budget_limit) {
    return response()->json(['success' => false, 'message' => 'Budget exceeded']);
}

// ❌ WRONG — Direct model mutation in controller
Transaction::create($request->all());

// ❌ WRONG — DB query or calculation in controller
$total = DB::table('transactions')->sum('amount');

// ❌ WRONG — Multi-step logic in controller (belongs in service with DB::transaction)
$transaction = Transaction::create([...]);
ApprovalRequest::create(['transaction_id' => $transaction->id, ...]);
BudgetLedger::decrement('balance', $request->amount);
```

**What NEVER belongs in a controller:**

- `Model::where(...)`, `Model::find(...)`, `DB::table(...)` — use service
- Business conditions (`if ($user->role === ...)`, budget checks, threshold logic) — use service
- Data transformation or calculations — use service
- `DB::transaction(...)` blocks — use service
- Direct `->create()`, `->update()`, `->delete()` on models — use service

### JSON Response & Controller AJAX Standard

All controller methods that return JSON (AJAX endpoints) MUST follow this standard:

**Required JSON format:**

```php
// Success
return response()->json([
    'success' => true,
    'message' => 'Data saved successfully.',
    'data'    => $result,
]);

// Error / Failure
return response()->json([
    'success' => false,
    'message' => 'Error description.',
    'data'    => null,
], 422); // or 500
```

**Mandatory try-catch for all AJAX methods:**

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
    ]);

    try {
        $result = $this->exampleService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data created successfully.',
            'data'    => $result,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to create data: ' . $e->getMessage(),
            'data'    => null,
        ], 500);
    }
}
```

**Rules:**

- NEVER return JSON without a `success` boolean key from AJAX endpoints
- NEVER let exceptions bubble unhandled in AJAX methods
- Use `$request->validate()` for simple validation; use `Validator::make()` for complex conditional rules
- Log errors in the catch block via `Log::error()` or the injected `LogService`

### Blade URL Convention

In Blade templates and inline JavaScript, **always** use the `route()` helper. Never hardcode URL paths.

**Patterns:**

```blade
{{-- Static route --}}
let url = "{{ route('module.store') }}";

{{-- Route with dynamic ID (for AJAX) --}}
let url = "{{ route('module.update', ':id') }}".replace(':id', id);

{{-- HTML form action --}}
<form action="{{ route('module.store') }}" method="POST">

{{-- HTML href --}}
<a href="{{ route('module.index') }}">Back</a>

{{-- HTML href with model ID --}}
<a href="{{ route('module.edit', $item->id) }}">Edit</a>
```

**Why `.replace(':id', id)` pattern?**
`route()` is server-side PHP; it cannot receive a JavaScript variable directly. The pattern `route('module.update', ':id')` renders `/module/:id` server-side, then `.replace(':id', id)` substitutes the JS value at runtime.

**What NOT to do:**

```blade
{{-- ❌ WRONG --}}
let url = '/module/' + id;
let url = `/module/${id}`;
```

### AJAX & SweetAlert2 Standard

SweetAlert2 (`Swal`) is globally available. All AJAX calls in Blade views MUST use it for user feedback.

**Standard AJAX pattern:**

```javascript
$.ajax({
    url: "{{ route('module.store') }}",
    method: "POST",
    data: formData,
    processData: false,
    contentType: false,
    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
    beforeSend: function () {
        Swal.fire({
            title: "Menyimpan...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });
    },
    success: function (response) {
        if (response.success) {
            Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: response.message,
                timer: 1500,
                showConfirmButton: false,
            }).then(() => {
                /* e.g. reload table, close modal */
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Gagal",
                text: response.message,
            });
        }
    },
    error: function (xhr) {
        let message = "Terjadi kesalahan pada server.";
        if (xhr.status === 422) {
            message = xhr.responseJSON?.message ?? "Input tidak valid.";
            Swal.fire({ icon: "warning", title: "Validasi", text: message });
        } else {
            message = xhr.responseJSON?.message ?? message;
            Swal.fire({ icon: "error", title: "Error", text: message });
        }
    },
});
```

**Standard DELETE confirmation pattern:**

```javascript
function deleteRecord(id) {
    let url = "{{ route('module.destroy', ':id') }}".replace(":id", id);
    Swal.fire({
        title: "Hapus data ini?",
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Ya, Hapus!",
        cancelButtonText: "Batal",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
                beforeSend: () =>
                    Swal.fire({
                        title: "Menghapus...",
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    }),
                success: (response) => {
                    if (response.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Dihapus!",
                            timer: 1200,
                            showConfirmButton: false,
                        }).then(() => table.ajax.reload());
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Gagal",
                            text: response.message,
                        });
                    }
                },
                error: (xhr) =>
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: xhr.responseJSON?.message ?? "Server error.",
                    }),
            });
        }
    });
}
```

**`beforeSend` title convention:**
| Context | Title |
|---------|-------|
| Create / Update | `'Menyimpan...'` |
| Delete | `'Menghapus...'` |
| Processing | `'Memproses...'` |
| Loading data | `'Memuat data...'` |
| Upload file | `'Mengupload...'` |

**SweetAlert2 icon convention:**
| Condition | `icon` | `title` |
|-----------|--------|--------|
| `response.success === true` | `'success'` | `'Berhasil'` |
| `response.success === false` | `'error'` | `'Gagal'` |
| HTTP 422 validation | `'warning'` | `'Validasi'` |
| HTTP 500 server error | `'error'` | `'Error'` |
| Confirm destructive action | `'warning'` | `'Konfirmasi'` |

**What NOT to do:**

- ❌ Never use native `alert()` or `confirm()` for user feedback
- ❌ Never show a success alert on an error condition
- ❌ Never skip `beforeSend` loading state on AJAX calls

### Modal Standard (Bootstrap 5)

All modals use Bootstrap 5 attributes (`data-bs-*`). Follow this structure:

**Form modal template:**

```blade
<div class="modal fade" id="modalFeatureName" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-lg me-1"></i>Add Feature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="featureForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Field One</label>
                            <input type="text" name="field_one" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Field Two</label>
                            <input type="text" name="field_two" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
```

**Modal rules:**

- Use `data-bs-toggle="modal"` and `data-bs-target="#modalId"` (Bootstrap 5, NOT `data-toggle`)
- Use `data-bs-dismiss="modal"` on close buttons
- Use native Bootstrap 5 `btn-close` for the X button in the header
- `modal-lg` for forms with multiple fields; `modal-md` (default) for simple confirmations
- `modal-dialog-centered` for vertical centering
- Close button in footer: `btn btn-light`
- Submit button in footer: `btn btn-primary`
- Always place `modal-footer` **inside** the `<form>` tag so the submit button works
- For file uploads: add `enctype="multipart/form-data"` to the form

**Form layout rules inside modal:**

- 2 fields per row: `col-md-6`
- Full-width field (textarea, file, long text): `col-12`
- Use `row g-3` as the container inside `modal-body`
- Select dropdowns: always include `<option value="">-- Pilih --</option>` as placeholder

**Icon convention (modal header title):**
| Purpose | Icon |
|---------|------|
| Add / Create | `bi bi-plus-lg` |
| Edit / Update | `bi bi-pencil-square` |
| Delete | `bi bi-trash` |
| View / Detail | `bi bi-eye` |
| Upload | `bi bi-upload` |

### Action Button Standard

Follow consistent button classes across all views:

**Card header (Add/New button):**

```blade
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
    <i class="bi bi-plus-lg me-1"></i> Add New
</button>
```

**DataTable action column (Edit / Delete):**

```php
// In Controller's addColumn('action', ...)
'<button class="btn btn-sm btn-outline-primary edit-btn" data-id="' . $row->id . '">
    <i class="bi bi-pencil-square"></i>
</button>
<button class="btn btn-sm btn-outline-danger delete-btn" data-id="' . $row->id . '">
    <i class="bi bi-trash"></i>
</button>'
```

**Button class reference:**
| Action | Class | Icon |
|--------|-------|------|
| Add (card header) | `btn btn-primary` | `bi bi-plus-lg` |
| Edit (table row) | `btn btn-sm btn-outline-primary` | `bi bi-pencil-square` |
| Delete (table row) | `btn btn-sm btn-outline-danger` | `bi bi-trash` |
| View/Detail (table row) | `btn btn-sm btn-outline-info` | `bi bi-eye` |
| Modal Close / Cancel | `btn btn-light` | — |
| Modal Save / Submit | `btn btn-primary` | — |
| Modal Delete Confirm | `btn btn-danger` | `bi bi-trash` |

## Development Workflow

### Starting Development

```bash
composer dev  # Runs server, queue, logs, vite concurrently
# OR separately:
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

### Database Operations

```bash
php artisan migrate
php artisan migrate:fresh --seed  # Reset with seeding
php artisan db:seed --class=SeederName
```

### Testing

```bash
php artisan test                           # All tests
php artisan test tests/Feature/TransactionTest.php
php artisan test --filter=ApprovalServiceTest
```

## Naming Conventions

| Item        | Convention              | Example                                  |
| ----------- | ----------------------- | ---------------------------------------- |
| Model       | PascalCase, Singular    | `Transaction`, `KPIWorkPlan`             |
| Controller  | PascalCase + Controller | `TransactionController`                  |
| Table       | snake_case, plural      | `transactions`, `kpi_workplans`          |
| Route name  | kebab-case with dot     | `transaction.store`, `budget.user.index` |
| View folder | kebab-case              | `pages/work-plan/`                       |
| Variable    | camelCase               | `$totalAmount`, `$pendingApprovals`      |
| Constant    | UPPER_SNAKE_CASE        | `STATUS_PENDING`                         |

## Critical Rules (Auto-Reject if Violated)

1. **NO direct Model queries in Controllers** - Always delegate to Services (`Model::where(...)`, `Model::find(...)`, `DB::table(...)` forbidden in controllers)
2. **NO business logic in Controllers** - Controllers are orchestrators only; conditions, calculations, and transformations belong in Services
3. **NO `DB::transaction()` in Controllers** - Multi-step operations with rollback must be wrapped inside Service methods
4. **SoftDeletes required** on: `transactions`, `approvals`, `workplans`, `budget_items`, audit-critical tables
5. **Permission middleware** on all protected routes: `->middleware('permission:module.action')`
6. **DB transactions** for multi-step operations: `DB::transaction(function() {...})` inside Services
7. **NO hardcoded IDs** - Use config, seeded data, or relationships
8. **Validate all inputs** in controller before passing to services
9. **NO raw SQL** for user input - Use query builder with bindings
10. **Try-catch required** on all AJAX controller methods that return `response()->json()`
11. **`success` key required** in all JSON AJAX responses: `['success' => bool, 'message' => ..., 'data' => ...]`
12. **Always use `route()` helper** in Blade templates - never hardcode URL strings
13. **Always eager load** relationships in DataTable queries (`->with([...])`) to prevent N+1

## Technology Stack

- **Framework:** Laravel 12, PHP 8.2+
- **Frontend:** Blade + Bootstrap 5 + jQuery 3.7 + SweetAlert2 + Livewire 3 + Tailwind 4 (via Vite)
- **Database:** MySQL/MariaDB
- **Key Packages:**
    - `spatie/laravel-permission` (^6.23) - RBAC
    - `yajra/laravel-datatables-oracle` (^12.6) - Server-side DataTables
    - `maatwebsite/excel` (^3.1) - Excel import/export
    - `power-components/livewire-powergrid` (^6.6) - DataGrid
- **Icons:** Bootstrap Icons (`bi bi-*`) and Remix Icons (`ri-*`)
- **Note:** Bootstrap 5 & SweetAlert2 are loaded as static assets (not npm). jQuery is available globally.

## Permission Helper

Global helper at `app/Helpers/PermissionHelper.php` (autoloaded):

```php
PermissionHelper::registerMenuPermission('transaction');
// Creates: transaction.view, .create, .edit, .delete
```

## Model Pattern

```php
class Example extends Model {
    use SoftDeletes;  // Required for important data

    // 1. Constants for status/enum
    const STATUS_PENDING = 0;

    // 2. Relationships
    // 3. Scopes: scopePending(), scopeByYear()
    // 4. Helpers: isPending(), getStatusLabel(), getStatusBadgeClass()
}
```

## Route Pattern

```php
Route::get('/', [Controller::class, 'index'])->name('module.index');
Route::post('/', [Controller::class, 'store'])->name('module.store');
Route::get('/data', [Controller::class, 'getData'])->name('module.data');  // DataTables endpoint
Route::post('/{id}/approve', [Controller::class, 'approve'])->name('module.approve');
```

## Additional Documentation

- **Full coding rules:** [ANTIGRAVITY_RULES.md](../ANTIGRAVITY_RULES.md) - Comprehensive standards
- **Approval system:** [documentasi/APPROVAL_SYSTEM.md](../documentasi/APPROVAL_SYSTEM.md)
- **Workplan module:** [documentasi/WORKPLAN_MODULE.md](../documentasi/WORKPLAN_MODULE.md)
- **Employee ID generator:** [documentasi/EMPLOYEE_ID_GENERATOR.md](../documentasi/EMPLOYEE_ID_GENERATOR.md)

## Common Pitfalls

1. Forgetting to bind service interface in `CustomServiceProvider`
2. Using Livewire without forcing Bootstrap 5 config
3. Not using DB transactions for approval chain creation
4. Modifying approval templates without considering in-flight requests (they use snapshots)
5. Accessing models directly in controllers instead of through services
6. Writing business conditions (`if/else` rules, budget checks) inside controllers — move to service
7. Placing `DB::transaction()` in the controller — it belongs inside the service method
8. Using Bootstrap 4 `data-toggle`/`data-target` instead of Bootstrap 5 `data-bs-toggle`/`data-bs-target`
9. Hardcoding URLs in Blade JS (e.g. `` `/module/${id}` ``) instead of using `route()` with `.replace(':id', id)`
10. Forgetting `rawColumns()` in DataTables when action column contains HTML
11. Missing try-catch in AJAX controller methods causing unhandled 500 errors returned as HTML
12. Using `FromCollection` on large dataset exports — use `FromQuery` to prevent memory exhaustion
13. Using native `alert()` instead of `Swal.fire()` for AJAX feedback
14. Missing `success` key in JSON response — Blade AJAX handlers check `response.success`, not just HTTP status
