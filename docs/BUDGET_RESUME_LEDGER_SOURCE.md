# Budget Resume Ledger Source

## Business Behavior

Budget Resume / Budget Control must use `budget_mutations` as the source of truth for approved budget values and realized usage.

The page is intended for admins to answer four questions:
- Total approved budget.
- Budget already used.
- Budget currently in approval submission process.
- Remaining balance after approved usage and in-process exposure.

## Calculation Rules

Per `workplan_budget_items` row:
- **Amount / Total Anggaran**:
  - CREDIT `INITIAL_BUDGET`
  - CREDIT `BUDGET_AMENDMENT`
  - CREDIT `BUDGET_RELOCATION_IN`
  - minus DEBIT `BUDGET_AMENDMENT`
  - minus DEBIT `BUDGET_RELOCATION_OUT`
- **Realization / Sudah Digunakan**:
  - DEBIT `CASH_ADVANCE`
  - DEBIT `LPJ_REIMBURSE`
  - minus CREDIT `LPJ_REFUND`
- **Submission / Proses Pengajuan**:
  - Sum of `transaction_details.estimated_total` for transactions with `status = STATUS_PROGRESS` and `status_approval` in `pending` or `in_progress`.
  - These are not yet in the ledger, but are shown as pending exposure.
- **Balance / Sisa Saldo**:
  - Current ledger balance (`total credit - total debit`) minus pending submission exposure.

Rows without `budget_mutations` are not shown because they do not represent approved ledger-backed budget.

## Budget Code Filter

Budget Resume must not preload every row from `budget_code` into the Blade page. The Budget Code filter uses Choices.js with server-side search and infinite scroll:

- Initial page load renders only `All Budget Codes` and, when an existing filter is active, the single selected budget code label.
- `GET /budget-resume/budget-codes/search?q=&limit=10&page=1` returns active budget codes in pages.
- The dropdown loads page 1 when opened, filters on typed search text, and requests the next page when the Choices.js list is scrolled near the bottom.
- `GET /budget-resume/budget-codes/by-code?code=<budget_code>` supports exact active-code lookup for filter preselection.
- AJAX URLs are passed from Blade through `window.budgetResumeRoutes`; JavaScript must not hardcode these endpoints.

## Touched Modules

- `app/Services/BudgetResumeService/`
  - Holds all Budget Resume business logic and ledger calculations.
  - Provides paginated active budget code search for the filter dropdown.
- `app/Http/Controllers/BudgetResumeController.php`
  - Controller only delegates request filters/search parameters to the service and returns Blade/JSON responses.
- `routes/web.php`
  - Registers named routes for Budget Resume index and budget code search/preselection endpoints.
- `resources/views/pages/budget/budget-resume.blade.php`
  - Displays ledger-backed summary cards and table values.
  - Budget code filter uses `budget_code.budget_code` through Choices.js server-side search instead of rendering all options.
- `app/Providers/CustomServiceProvider.php`
  - Binds `BudgetResumeService` to `BudgetResumeServiceImpl`.

## Testing

Covered by `tests/Feature/Services/BudgetResumeServiceTest.php`:
- Budget Resume uses ledger mutations instead of `workplan_budget_items.total`.
- Realization is net usage after LPJ refund/reimburse.
- Pending approval submissions are included as in-process exposure.
- Approved/draft transactions are not counted as pending submission.
- Budget code search is paginated and excludes inactive codes.
- Budget code exact lookup returns only active codes for filter preselection.
