# Budget User Cancel Verification Flow

## Ringkasan

Flow ini memungkinkan user membatalkan proses price verification setelah item di-submit untuk verification, selama belum ada verifier yang memproses item tersebut. Setelah dibatalkan, item kembali dapat diedit atau dihapus. Jika user submit ulang, sistem akan membuat snapshot kandidat verifier baru dari matriks price verification yang sedang aktif.

## Kondisi Bisnis

Cancel verification hanya berlaku untuk item dengan kondisi:

- `workplan_budget_items.status = draft`
- `workplan_budget_items.verification_status = pending`
- belum ada row `workplan_budget_approver.is_executor = true` untuk item tersebut

Cancel verification tidak boleh dilakukan jika:

- item sudah masuk proses approval (`status` bukan `draft`)
- item tidak sedang pending verification
- sudah ada verifier yang memproses item

## Status Transition

Submit for verification:

```text
draft + unverified/rejected -> draft + pending
```

Submit verification mengunci row `workplan_budget_items` dengan `lockForUpdate()` sejak awal transaksi. Ini mencegah double-click/request paralel membuat kandidat verifier dan notifikasi ganda.

Cancel verification:

```text
draft + pending -> draft + unverified
```

Verify by verifier:

```text
draft + pending -> pending + verified
```

Reject by verifier:

```text
draft + pending -> draft + rejected
```

## Data Contract

Endpoint:

```text
POST /budget-verification/{itemId}/cancel
```

Route name:

```text
verification.budget.cancel
```

Success response:

```json
{
  "success": true,
  "message": "Proses verifikasi berhasil dibatalkan. Item dapat diedit atau dihapus kembali.",
  "data": {
    "item_id": 1,
    "deleted_candidates": 2,
    "deleted_notifications": 2
  }
}
```

## Data Cleanup

Saat cancel berhasil:

- hapus snapshot kandidat verifier di `workplan_budget_approver`
- set `workplan_budget_items.verification_status` menjadi `unverified`
- pastikan `workplan_budget_items.status` tetap/kembali `draft`
- reset `workplan_budget_items.price_final` ke `0`
- hapus notifikasi `verification` yang terkait item tersebut

Snapshot verifier sengaja dihapus agar submit ulang selalu mengambil matriks price verification terbaru dari konfigurasi:

- `price_verification`
- `price_verification_code`
- `price_verification_user`
- `employment`

## Notification Cleanup

Notifikasi workflow budget item harus memakai referensi agar bisa dibersihkan saat proses dimundurkan:

```text
reference_type = workplan_budget_item_verification
reference_id = workplan_budget_items.id
```

Untuk approval item budget:

```text
reference_type = workplan_budget_item_approval
reference_id = workplan_budget_items.id
```

Saat cancel verification, sistem menghapus notifikasi kategori `verification` untuk `reference_type = workplan_budget_item_verification` dan `reference_id` item tersebut. Sistem juga menjalankan fallback cleanup untuk notifikasi lama yang belum punya reference dengan mencocokkan kategori, judul, dan detail.

Saat cancel approval request, sistem menghapus notifikasi kategori `approval` untuk pending approver yang belum memproses approval. Ini mencegah user yang sudah tidak perlu approve tetap melihat notifikasi approval di header.

Kolom notifikasi yang digunakan:

- `notifications.reference_type`
- `notifications.reference_id`

## Modul Yang Terlibat

- `routes/web.php`
- `app/Http/Controllers/VerificationBudgetController.php`
- `app/Services/VerificationBudgetService/VerificationBudgetService.php`
- `app/Services/VerificationBudgetService/VerificationBudgetServiceImpl.php`
- `app/Services/WorkplanBudgetItemApprovalService.php`
- `app/Services/NotificationService.php`
- `app/Models/WorkplanBudgetItem.php`
- `public/assets/js/budget-user.js`
- `database/migrations/2026_06_06_000001_add_reference_columns_to_notifications_table.php`

## UI Behavior

Di halaman budget user, item dengan status `draft + pending verification` menampilkan:

- badge `Waiting Verification`
- tombol view verification status
- tombol cancel verification

Setelah cancel berhasil, tabel budget item, daftar pending verification, dan badge/list notifikasi header di-refresh.

## Guard Edit dan Delete

`WorkplanBudgetItem::canBeEdited()` mengembalikan `false` jika `verification_status = pending`. Artinya item pending verification tidak dapat diedit atau dihapus melalui UI maupun request langsung sampai proses verification dibatalkan atau ditolak oleh verifier.

## Testing dan Caveat

Verifikasi yang dilakukan:

- PHP syntax check pada file yang berubah
- route check untuk `verification.budget.cancel`
- JavaScript syntax check untuk `public/assets/js/budget-user.js`
- migration SQL check dengan `php artisan migrate --pretend --path=database/migrations/2026_06_06_000001_add_reference_columns_to_notifications_table.php`

`composer test` saat ini belum dapat berjalan penuh karena test suite existing berhenti di `tests/Unit/Services/DashboardServiceTest.php` dengan error `Call to undefined function uses()`. Error ini terkait setup test/Pest existing, bukan flow cancel verification.
