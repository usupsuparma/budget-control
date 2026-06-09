# Budget Control AI Coding Agent Instructions

## Project Overview

Budget Control is a Laravel 12 enterprise application for budget management, KPI tracking, and approval workflows with two-phase dynamic approval system (uppline chain → master flow with threshold-based routing).

**Operational Status Lifecycle:**
1. **Status 2 (Approved):** Automatically set when transaction approval chain is completed.
2. **Status 3 (Paid):** Automatically set when LPJ (Laporan Pertanggungjawaban) is fully approved.
3. **Status 4 (Completed):** Final state set via external API/Webhook (`/api/v1/webhook/transaction/complete`) to synchronize with external payment/finance systems.

**Key Documentation:**
- [Budget Resume Ledger Source](docs/BUDGET_RESUME_LEDGER_SOURCE.md) - Budget Resume / Budget Control uses `budget_mutations` as source of truth and paged Choices.js search for the Budget Code filter.
- [Budget Verification Auto-Submit Logging](docs/BUDGET_VERIFICATION_AUTO_SUBMIT_LOGGING.md) - Adds `debug_ref` tracing from verification to workplan budget item approval so auto-submit failures can be traced from UI messages into Laravel logs.
- [Budget User Pending Approval Notification Sync](docs/BUDGET_USER_PENDING_APPROVAL_NOTIFICATION_SYNC.md) - Keeps Budget User pending approval badge/list in sync and removes stale approval task notifications.
- [Budget User Cancel Verification Flow](docs/BUDGET_USER_CANCEL_VERIFICATION_FLOW.md) - Cancel pending price verification before verifier processing, clean stale workflow notifications, reset item editability, and regenerate verifier snapshot on resubmit.
- [Budget Submission Tab UI](docs/BUDGET_SUBMISSION_TAB_UI.md) - Consistent custom tab styling for the Budget Movement page, aligned with Budget User tabs.
- [Laravel CI Workflow](docs/LARAVEL_CI_WORKFLOW.md) - GitHub Actions pipeline for Laravel/PHP/MySQL backend tests.
- [Settings User Module Tab](docs/SETTINGS_USER_MODULE_TAB.md) - `Users & Roles` page now includes a `Modul` tab for CRUD on `modul_menu`, including duplicate prevention and delete protection when permissions still reference a module.
- [User Submission Program Current Year Filter](docs/USER_SUBMISSION_PROGRAM_CURRENT_YEAR_FILTER.md) - Program ID dropdowns on User Submission only load KPI Workplans from the current year.
- [Employee Org Resolution](documentasi/EMPLOYEE_ORG_RESOLUTION.md) - How to determine user's Division, Department, and Section.
- [Employee Division Display Fix](documentasi/EMPLOYEE_DIVISION_DISPLAY_FIX.md) - Bug fix history and rules for level-aware Division name resolution (`getDivisionName()`).
- [MacframeGA Import](documentasi/MACFRAME_GA_IMPORT.md) - Two-phase import workflow for external MacframeGA data.
- [Transaction Approval and LPJ Status Workflow](documentasi/TRANSACTION_APPROVAL_LPJ_STATUS_WORKFLOW.md) - Transaction status lifecycle, LPJ eligibility, and proof-file preview behavior.
- [Budget Ledger](documentasi/Budget Ledger.md) - Pure ledger source of truth, budget mutation categories, and balance formula.
- [Sidebar Route Name Standard](documentasi/SIDEBAR_ROUTE_NAME_STANDARD.md) - Sidebar links and active/collapse states must use named routes.
- [User Role Service](documentasi/USER_ROLE_SERVICE.md) - Single source of truth for admin/scoped role checks. Update `ADMIN_ROLES` constant here when role names change.

## Critical Architecture Patterns

### Service Layer Pattern (MANDATORY)

All business logic MUST use the Interface + Implementation pattern. Controllers are orchestrators only.

**Rules:**
- **Atomicity:** `DB::transaction` MUST be placed inside the Service implementation, NOT in the Controller. Use the closure-based method: `DB::transaction(fn() => ...)`.
- **Single Responsibility:** One service method = one business use case.
- **Interface-First:** Always define the contract in the Interface before implementing.
- **Legacy Refactoring:** If you encounter legacy code with CRUD or business logic in the Controller, you MUST refactor it into the appropriate Service when modifying that module.
- **Testing Mandate:** Every service method (new or refactored) MUST have a corresponding Automated Test (PHPUnit) to ensure logic integrity.

**Directory structure:**
```
app/Services/{ServiceName}/
├── {ServiceName}Service.php       # Interface (contract)
├── {ServiceName}ServiceImpl.php   # Implementation (logic + transactions)
└── DTOs/                          # Data Transfer Objects (if needed)
```

**Binding in `app/Providers/CustomServiceProvider.php` (Mandatory Manual Binding):**
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
For complex services, use `readonly class` (PHP 8.2+) or a strictly defined array. DTOs MUST be placed within the specific service directory under `DTOs/`.
```php
// app/Services/TransactionService/DTOs/TransactionData.php
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

### Role-Based Access Control (UserRoleService)

All admin vs. non-admin checks MUST go through `UserRoleService`. **NEVER** call `hasRole()` or `hasAnyRole()` inline in Services or Controllers for this purpose.

```php
// CORRECT
$isAdmin = $this->userRoleService->isAdmin($user);
$divisionIds = $this->userRoleService->getDivisionIds($user);

// WRONG — do not do this
$isAdmin = $user->hasRole('Admin') || $user->hasRole('super-admin');
```

**Admin roles** (sees all data) are defined exclusively in `UserRoleServiceImpl::ADMIN_ROLES`.
When a role is renamed in the database, **only that constant needs to change**.

See full reference: [User Role Service](documentasi/USER_ROLE_SERVICE.md)

### Approval System Architecture

Two-phase sequential approval with immutable snapshots:
1. **Phase 1: Uppline Chain** - Follows `users.uppline_id` recursively until NULL.
2. **Phase 2: Master Flow** - Threshold-based (`amount <= threshold`) or all-levels mode.

**Snapshot Rule:**
When an approval request is created, MUST save a JSON snapshot of the source data (e.g., in `approval_flow_details`) to ensure history remains valid even if master data changes.

### Budget Movement Architecture

Budget Movement / Budget Submission uses `workplan_budget_items` as the budget account source. `budget_submissions.budget_account_id` is the target `workplan_budget_items.id`; relocation also requires `source_budget_account_id` as the source `workplan_budget_items.id`.
Source and target budget items must be approved and belong to the selected `budget_submissions.work_plan_id`.

When a budget submission is fully approved, approval MUST record append-only rows in `budget_mutations` before marking the submission as approved:
- Add Budget: one CREDIT mutation with category `BUDGET_AMENDMENT`.
- Relocation: one DEBIT mutation from source with category `BUDGET_RELOCATION_OUT` and one CREDIT mutation to target with category `BUDGET_RELOCATION_IN`.

Relocation must validate source balance using `BudgetLedgerService::getBudgetBalance()` and must be idempotent through `budget_mutations.budget_submission_id`.

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

### Workflow Notification References

Notifications created for workflow tasks that can be cancelled, rejected, resubmitted, or otherwise invalidated MUST include `notifications.reference_type` and `notifications.reference_id`.

When a workflow is moved backward or cancelled, the same service method that changes the workflow status MUST also delete or invalidate pending task notifications for users who no longer need to act.

Examples:
- Budget item verification uses `reference_type = workplan_budget_item_verification` and `reference_id = workplan_budget_items.id`.
- Budget item approval uses `reference_type = workplan_budget_item_approval` and `reference_id = workplan_budget_items.id`.

### Blade & JavaScript Standard

- **URL Helper:** ALWAYS use `route('name', ':id').replace(':id', id)`.
- **JS Routes:** NEVER hardcode URLs in AJAX calls. ALWAYS pass routes from Blade to JS using a global object or data attributes.
- **Choices.js Standard:** All `<select>` elements MUST use **Choices.js** with individual instances. Refer to [Choices.js Standard](documentasi/CHOICES_JS_STANDARD.md) for implementation details.
- **Feedback:** ALWAYS use SweetAlert2 (`Swal.fire`).
- **Loading:** ALWAYS show `Swal.showLoading()` in `beforeSend`.
- **Data-Driven UI:** ALWAYS use JavaScript arrays/objects (populated via AJAX) as the source of truth for synchronizing fields. Avoid storing business data in DOM attributes (`data-*`) for multiple related fields.
- **Sidebar Navigation:** Sidebar links MUST use named routes via `route()`, and active/collapse checks MUST use `request()->routeIs()` instead of URL path matching such as `Request::is()`.

### Documentation Update Standard

Every feature change, workflow change, status mapping change, API/route change, or user-visible behavior change MUST update documentation in the same task.

**Rules:**
- New flow/workflow documentation MUST be created under `docs/`.
- Every new flow document under `docs/` MUST be linked from `AGENTS.md` in the same change set.
- Update the relevant file under `documentasi/` when existing documentation covers the feature.
- Create a new focused document under `docs/` when no suitable document exists.
- Update `AGENTS.md` when the change introduces a durable rule, architectural convention, workflow invariant, or important reference document.
- Documentation updates must describe the business behavior, touched modules/files, status codes or data contract changes, and any testing or operational caveats.

## Critical Rules (Auto-Reject if Violated)

1. **NO Model queries/CRUD in Controllers.**
2. **NO `DB::transaction` in Controllers** - Move to Service Implementation using closure `DB::transaction(fn() => ...)`.
3. **NO raw arrays for complex data** - Use FormRequest/DTO.
4. **Refactor on Sight:** Move any legacy Controller-based CRUD/logic to Services when modifying a module (unless it's a very minor fix).
5. **Zero-Test Tolerance:** All new or refactored logic must include automated tests (PHPUnit).
6. **SoftDeletes required** on all audit-critical tables (Transactions, Budgets, Approvals). Master data currently do not use SoftDeletes.
7. **Eager Load everything** - N+1 is a blocker.
8. **Immutable Snapshots** for all approval-related data.
9. **Custom Exceptions** for business logic errors.
10. **Bootstrap 5 + Swal2** for UI/UX consistency.
11. **Mandatory Choices.js:** Every select dropdown must implement Choices.js individual instances.
12. **Data-Driven Updates:** Synchronize related form fields using JavaScript data objects instead of DOM `data-*` attributes.
13. **Library Stewardship:** ALWAYS check `public/assets/libs/` and `TECHNICAL_STACK.md` before adding any new frontend libraries or CDN links. Use local assets via `asset()` helper whenever possible.
14. **Documentation Must Stay Current:** Every feature or workflow change must update the relevant `docs/` or `documentasi/` file and, when the rule is durable, `AGENTS.md` in the same change set. New flow documentation belongs in `docs/`.
15. **Level-Aware Division Resolution:** NEVER use `$jobPosition->structure` (i.e. `JobPosition::structure()`) directly to display a Division name. It is only valid for L2. For all levels use `Employment::getDivisionName()`. See [Employee Division Display Fix](documentasi/EMPLOYEE_DIVISION_DISPLAY_FIX.md).

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
