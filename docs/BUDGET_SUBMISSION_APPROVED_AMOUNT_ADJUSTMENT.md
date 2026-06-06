# Budget Submission Approved Amount Adjustment

## Tujuan

Flow ini memungkinkan final approver mengubah nominal Budget Movement saat approval tanpa menghilangkan nominal pengajuan asli dari user.

Contoh: user mengajukan Rp 5.000.000, tetapi final approver hanya menyetujui Rp 4.000.000.

## Alur

1. User membuat Budget Movement dengan tipe `Add Budget` atau `Relocation`.
2. User mengisi `estimation_amount` sebagai nominal pengajuan.
3. Pada approval terakhir, sistem menampilkan input `Approved Amount`.
4. Default `Approved Amount` sama dengan `estimation_amount`.
5. Final approver dapat mengubah nominal tersebut sebelum approve.
6. Sistem menyimpan nominal final ke `budget_submissions.approved_amount`.
7. Jika nominal final berbeda dari pengajuan, sistem mengisi:
   - `approved_amount_changed_by`
   - `approved_amount_changed_at`
8. Ledger memakai `approved_amount` sebagai nominal mutasi. Jika `approved_amount` kosong, ledger fallback ke `estimation_amount`.

## Tampilan

- User tetap dapat melihat nominal pengajuan.
- Jika final approved amount berbeda, UI menampilkan nilai requested dan approved.
- Bulk approve tidak digunakan untuk final approval yang membutuhkan input nominal.

## File Terkait

- `resources/views/pages/budget/budget-submission.blade.php`
- `app/Http/Controllers/BudgetSubmissionController.php`
- `app/Models/BudgetSubmission.php`
- `app/Services/BudgetSubmissionApprovalService/BudgetSubmissionApprovalServiceImpl.php`
- `app/Services/BudgetLedgerService/BudgetLedgerServiceImpl.php`
- `database/migrations/2026_06_07_000001_add_approved_amount_to_budget_submissions_table.php`
