# Budget Control AI Coding Agent Instructions

## Project Overview

Budget Control is a Laravel 12 enterprise application for budget management, KPI tracking, and approval workflows with two-phase dynamic approval system (uppline chain → master flow with threshold-based routing).

**Key Documentation:**
- [Employee Org Resolution](documentasi/EMPLOYEE_ORG_RESOLUTION.md) - How to determine user's Division, Department, and Section.

## Critical Architecture Patterns

### Service Layer Pattern (MANDATORY)

All business logic MUST use the Interface + Implementation pattern. Controllers are orchestrators only.

**Rules:**
- **Atomicity:** `DB::transaction` MUST be placed inside the Service implementation, NOT in the Controller.
- **Single Responsibility:** One service method = one business use case.
- **Interface-First:** Always define the contract in the Interface before implementing.
- **Legacy Refactoring:** If you encounter legacy code with CRUD or business logic in the Controller, you MUST refactor it into the appropriate Service when modifying that module.
- **Testing Mandate:** Every service method (new or refactored) MUST have a corresponding Automated Test (Pest/PHPUnit) to ensure logic integrity.

**Directory structure:**
```
app/Services/{ServiceName}/
├── {ServiceName}Service.php       # Interface (contract)
└── {ServiceName}ServiceImpl.php   # Implementation (logic + transactions)
```

**Binding in `app/Providers/CustomServiceProvider.php`:**
```php
$this->app->bind(
    \App\Services\ExampleService\ExampleService::class,
    \App\Services\ExampleService\ExampleServiceImpl::class
);
```

### Validation & Data Transfer (DTO)

To ensure type-safety and prevent bugs, data passing from Controller to Service MUST be structured.

**1. Form Request (Mandatory):**
NEVER validate in the Controller. Use `php artisan make:request`.
```php
public function store(StoreTransactionRequest $request) {
    $data = $request->validated(); // Guaranteed to be valid
    return $this->service->create($data);
}
```

**2. Data Transfer Object (DTO):**
For complex services, use `readonly class` (PHP 8.2+) or a strictly defined array.
```php
// app/DTOs/TransactionData.php
readonly class TransactionData {
    public function __construct(
        public int $amount,
        public int $unit_id,
        public string $description,
        public ?array $attachments = []
    ) {}
}
```

### Custom Domain Exceptions

Instead of returning `false` or generic errors, use Domain-Specific Exceptions to handle business logic failures.

**Pattern:**
- Create `app/Exceptions/DomainException.php` as base.
- Throw specific exceptions like `InsufficientBudgetException` or `ApprovalChainBrokenException`.
- Catch these in the Controller to return a meaningful AJAX response.

```php
// In Service
if ($budget < $amount) {
    throw new InsufficientBudgetException("Saldo tidak mencukupi untuk unit ini.");
}
```

### Approval System Architecture

Two-phase sequential approval with immutable snapshots:
1. **Phase 1: Uppline Chain** - Follows `users.uppline_id` recursively until NULL.
2. **Phase 2: Master Flow** - Threshold-based (`amount <= threshold`) or all-levels mode.

**Snapshot Rule:**
When an approval request is created, MUST save a JSON snapshot of the source data to ensure history remains valid even if master data changes.

### Eager Loading Standard (Anti N+1)

Standardize eager loading to prevent performance bottlenecks across the entire app.

- **Global:** Use `$with` property in Models for relations that are *always* needed.
- **DataTables:** MUST use `->with([...])` in the query builder.
- **Services:** Service methods returning Models MUST load necessary relations before returning.

### JSON Response & AJAX Standard

**Required JSON format:**
```php
return response()->json([
    'success' => true,
    'message' => 'Operation successful.',
    'data'    => $result,
]);
```

**Mandatory try-catch in Controllers:**
```php
try {
    $result = $this->service->execute($request->validated());
    return response()->json(['success' => true, 'data' => $result]);
} catch (DomainException $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
} catch (\Exception $e) {
    Log::error($e);
    return response()->json(['success' => false, 'message' => 'Internal Server Error'], 500);
}
```

### Blade & JavaScript Standard

- **URL Helper:** ALWAYS use `route('name', ':id').replace(':id', id)`.
- **JS Routes:** NEVER hardcode URLs in AJAX calls. ALWAYS pass routes from Blade to JS using a global object or data attributes.
- **Choices.js Standard:** All `<select>` elements MUST use **Choices.js** with individual instances. Refer to [Choices.js Standard](documentasi/CHOICES_JS_STANDARD.md) for implementation details.
- **Feedback:** ALWAYS use SweetAlert2 (`Swal.fire`).
- **Loading:** ALWAYS show `Swal.showLoading()` in `beforeSend`.
- **Data-Driven UI:** ALWAYS use JavaScript arrays/objects (populated via AJAX) as the source of truth for synchronizing fields. Avoid storing business data in DOM attributes (`data-*`) for multiple related fields.

## Critical Rules (Auto-Reject if Violated)

1. **NO Model queries/CRUD in Controllers.**
2. **NO `DB::transaction` in Controllers** - Move to Service.
3. **NO raw arrays for complex data** - Use FormRequest/DTO.
4. **Refactor on Sight:** Move any legacy Controller-based CRUD/logic to Services when modifying a module.
5. **Zero-Test Tolerance:** All new or refactored logic must include automated tests (Pest/PHPUnit).
6. **SoftDeletes required** on all audit-critical tables.
7. **Eager Load everything** - N+1 is a blocker.
8. **Immutable Snapshots** for all approval-related data.
9. **Custom Exceptions** for business logic errors.
10. **Bootstrap 5 + Swal2** for UI/UX consistency.
11. **Mandatory Choices.js:** Every select dropdown must implement Choices.js individual instances.
12. **Data-Driven Updates:** Synchronize related form fields using JavaScript data objects instead of DOM `data-*` attributes.
13. **Library Stewardship:** ALWAYS check `public/assets/libs/` and `TECHNICAL_STACK.md` before adding any new frontend libraries or CDN links. Use local assets via `asset()` helper whenever possible.

## Technology Stack
- Laravel 12 (PHP 8.2+)
- Blade + Livewire 3 + PowerGrid
- Bootstrap 5 + SweetAlert2 + jQuery 3.7
- Spatie Permission + Yajra DataTables

## Naming Conventions
- Model: `PascalCase` (Singular)
- Service: `PascalCaseService` & `PascalCaseServiceImpl`
- Route: `kebab-case.action`
- View: `kebab-case` folder structure
