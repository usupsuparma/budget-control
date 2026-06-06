# Budget Submission Add Budget Source Approval

## Tujuan

Flow ini memastikan `Add Budget` tetap memiliki dasar pengambilan budget. User pembuat Budget Movement hanya memilih budget tujuan, lalu approver terakhir memilih budget sumber yang akan didebit sebelum request disetujui penuh.

## Alur

1. User membuat Budget Movement dengan tipe `Add Budget`.
2. User memilih work plan, budget account tujuan, nominal, dan submit for approval.
3. Approval berjalan sesuai matriks approval aktif.
4. Pada approval terakhir untuk tipe `Add Budget`, sistem menampilkan input `Source Budget Account` di modal detail approval.
5. Approver terakhir wajib memilih source budget account sebelum approve.
6. Sistem menyimpan pilihan tersebut ke `budget_submissions.source_budget_account_id`.
7. Setelah semua approval selesai, ledger mencatat dua mutasi:
   - Source budget: `mutation_type = D`, `category = BUDGET_AMENDMENT`.
   - Target budget: `mutation_type = C`, `category = BUDGET_AMENDMENT`.

## Validasi

- Source budget wajib dipilih hanya pada approval terakhir untuk tipe `Add Budget`.
- Source budget harus budget item approved pada work plan yang sama.
- Source budget tidak boleh sama dengan budget tujuan.
- Saldo source budget harus cukup untuk nominal Add Budget.
- Item final Add Budget yang membutuhkan source tidak diproses melalui bulk approve.

## File Terkait

- `resources/views/pages/budget/budget-submission.blade.php`
- `app/Http/Controllers/BudgetSubmissionController.php`
- `app/Services/BudgetSubmissionApprovalService/BudgetSubmissionApprovalService.php`
- `app/Services/BudgetSubmissionApprovalService/BudgetSubmissionApprovalServiceImpl.php`
- `app/Services/BudgetLedgerService/BudgetLedgerService.php`
- `app/Services/BudgetLedgerService/BudgetLedgerServiceImpl.php`
- `app/Services/BudgetSubmissionService/BudgetSubmissionServiceImpl.php`
