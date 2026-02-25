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
- Exports: Extend `FromCollection`, implement `WithHeadings`, `WithMapping`
- Queue large operations: `->queue()` method
- See: `MarketingPlanImport.php`, `ProductionTemplateExport.php`

### Livewire Components
Uses Livewire 3 with PowerGrid for data tables:
- Force Bootstrap 5 in `boot()`: `config(['livewire-powergrid.framework' => 'bootstrap5'])`
- Example: `app/Livewire/EmployeeTable.php`
- Views in `resources/views/livewire/`

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
-   **Exclude Credentials:** NEVER log sensitive information such as passwords, API keys, tokens, or any other credentials. Filter out sensitive data from context arrays.
-   **Relevant Message:** Ensure the `message` string clearly describes the event being logged.
-   **Standard Context:** Always include `'class' => __CLASS__` and `'function' => __FUNCTION__` in the `$context` array to easily trace the origin of the log entry. Add other relevant data to the context, such as IDs, user information, or input parameters, but **never sensitive data**.
-   **Appropriate Level:** Use the correct log level (`info`, `warning`, `error`, `debug`, `notice`, `critical`, `alert`, `emergency`) to reflect the severity of the event.

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

| Item | Convention | Example |
|------|------------|---------|
| Model | PascalCase, Singular | `Transaction`, `KPIWorkPlan` |
| Controller | PascalCase + Controller | `TransactionController` |
| Table | snake_case, plural | `transactions`, `kpi_workplans` |
| Route name | kebab-case with dot | `transaction.store`, `budget.user.index` |
| View folder | kebab-case | `pages/work-plan/` |
| Variable | camelCase | `$totalAmount`, `$pendingApprovals` |
| Constant | UPPER_SNAKE_CASE | `STATUS_PENDING` |

## Critical Rules (Auto-Reject if Violated)

1. **NO direct Model queries in Controllers** - Always delegate to Services
2. **NO business logic in Controllers** - Controllers are orchestrators only
3. **SoftDeletes required** on: `transactions`, `approvals`, `workplans`, `budget_items`, audit-critical tables
4. **Permission middleware** on all protected routes: `->middleware('permission:module.action')`
5. **DB transactions** for multi-step operations: `DB::transaction(function() {...})`
6. **NO hardcoded IDs** - Use config, seeded data, or relationships
7. **Validate all inputs** before passing to services
8. **NO raw SQL** for user input - Use query builder with bindings

## Technology Stack
- **Framework:** Laravel 12, PHP 8.2+
- **Frontend:** Blade + Livewire 3 + Tailwind 4
- **Database:** MySQL/MariaDB
- **Key Packages:**
  - `spatie/laravel-permission` (^6.23) - RBAC
  - `yajra/laravel-datatables-oracle` (^12.6) - Server-side tables
  - `maatwebsite/excel` (^3.1) - Excel import/export
  - `power-components/livewire-powergrid` (^6.6) - DataGrid

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
