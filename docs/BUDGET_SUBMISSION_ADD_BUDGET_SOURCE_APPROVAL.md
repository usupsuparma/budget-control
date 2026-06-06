# Budget Submission Add Budget Source Approval

## Tujuan

Flow ini memastikan `Add Budget` tetap memiliki dasar pengambilan budget. User pembuat Budget Movement hanya memilih budget tujuan, lalu approver terakhir memilih budget sumber yang akan didebit sebelum request disetujui penuh.

## Alur

1. User membuat Budget Movement dengan tipe `Add Budget`.
2. User memilih work plan, budget account tujuan, nominal, dan submit for approval.
3. Approval berjalan sesuai matriks approval aktif.
4. Pada approval terakhir untuk tipe `Add Budget`, sistem menampilkan filter `Source Division`, `Source Work Plan`, lalu `Source Budget Account` di modal detail approval.
5. Approver terakhir mengisi `Approved Amount`. Default nilainya mengikuti nominal pengajuan user.
6. Approver terakhir wajib memilih source budget account dari filter tersebut sebelum approve.
7. Sistem menyimpan pilihan source ke `budget_submissions.source_budget_account_id`.
8. Sistem menyimpan nominal final ke `budget_submissions.approved_amount`.
9. Jika nominal final berbeda dari `budget_submissions.estimation_amount`, sistem mengisi `approved_amount_changed_by` dan `approved_amount_changed_at`.
10. Setelah semua approval selesai, ledger mencatat dua mutasi memakai nominal approved:
   - Source budget: `mutation_type = D`, `category = BUDGET_AMENDMENT`.
   - Target budget: `mutation_type = C`, `category = BUDGET_AMENDMENT`.

## Validasi

- Source budget wajib dipilih hanya pada approval terakhir untuk tipe `Add Budget`.
- Source budget harus budget item approved dari work plan yang dipilih approver.
- Filter `Source Division` menampilkan seluruh divisi agar approver terakhir bisa memilih sumber budget dari divisi lain.
- Source budget tidak boleh sama dengan budget tujuan.
- Approved amount wajib lebih dari 0.
- Saldo source budget harus cukup untuk nominal approved Add Budget.
- Item final Add Budget yang membutuhkan source tidak diproses melalui bulk approve.

## Nominal Pengajuan vs Approved

- `estimation_amount` tetap menjadi nominal yang diajukan user.
- `approved_amount` menjadi nominal yang disetujui final approver.
- Jika keduanya berbeda, UI menampilkan nominal requested dan approved agar user tahu ada perubahan.

## File Terkait

- `resources/views/pages/budget/budget-submission.blade.php`
- `app/Http/Controllers/BudgetSubmissionController.php`
- `app/Services/BudgetSubmissionApprovalService/BudgetSubmissionApprovalService.php`
- `app/Services/BudgetSubmissionApprovalService/BudgetSubmissionApprovalServiceImpl.php`
- `app/Services/BudgetLedgerService/BudgetLedgerService.php`
- `app/Services/BudgetLedgerService/BudgetLedgerServiceImpl.php`
- `app/Services/BudgetSubmissionService/BudgetSubmissionServiceImpl.php`
