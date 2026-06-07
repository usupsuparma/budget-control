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

## Touched Modules

- `app/Services/BudgetResumeService/`
  - Holds all Budget Resume business logic and ledger calculations.
- `app/Http/Controllers/BudgetResumeController.php`
  - Controller now only delegates request filters to the service and renders the Blade view.
- `resources/views/pages/budget/budget-resume.blade.php`
  - Displays ledger-backed summary cards and table values.
  - Budget code filter uses `budget_code.budget_code`.
- `app/Providers/CustomServiceProvider.php`
  - Binds `BudgetResumeService` to `BudgetResumeServiceImpl`.

## Testing

Covered by `tests/Feature/Services/BudgetResumeServiceTest.php`:
- Budget Resume uses ledger mutations instead of `workplan_budget_items.total`.
- Realization is net usage after LPJ refund/reimburse.
- Pending approval submissions are included as in-process exposure.
- Approved/draft transactions are not counted as pending submission.
