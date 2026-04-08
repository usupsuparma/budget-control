# Issue: Implementasi Bulk Verification (Checklist & CSV Upload) pada Page Budget User

## Deskripsi Masalah
Saat ini pada halaman `budget-user` tab **Pending Verification**, verifikator harus melakukan verifikasi (approve/reject) satu per satu. Jika terdapat banyak data (misal 100+), proses ini menjadi tidak efisien. Diperlukan fitur untuk melakukan seleksi banyak item (checklist) dan melakukan verifikasi masal, serta opsi untuk upload data verifikasi melalui file CSV.

## Rencana Implementasi

### 1. Perubahan UI (Frontend - Blade & JS) [DONE]
*   **File:** `resources/views/pages/budget/budget-user.blade.php` [DONE]
    *   Ubah tampilan di dalam `#pendingItemsContainer` dari format *Card* menjadi *Table* responsif. [DONE]
    *   Tambahkan kolom Checkbox di sebelah kiri setiap baris. [DONE]
    *   Tambahkan checkbox "Select All" di header tabel. [DONE]
    *   Tambahkan Toolbar di atas tabel yang berisi:
        *   Button `Bulk Verify` (Muncul saat ada item terpilih). [DONE]
        *   Button `Bulk Reject` (Muncul saat ada item terpilih). [DONE]
        *   Button `Import CSV` untuk verifikasi cepat via file. [DONE]
    *   Tambahkan Modal untuk input `fix_price` masal atau konfirmasi reject masal. [DONE]

*   **File:** `public/assets/js/budget-user.js` (Dalam kasus ini, script di dalam Blade) [DONE]
    *   Update fungsi `renderPendingVerificationItems(items)` untuk me-render format tabel dengan checkbox. [DONE]
    *   Buat event listener untuk checkbox (individual & select all). [DONE]
    *   Buat fungsi `handleBulkVerify()`: Mengumpulkan ID yang dipilih dan menampilkan modal input harga (jika harga ingin disamakan) atau langsung kirim jika harga sudah ada di field input baris. [DONE]
    *   Buat fungsi `handleBulkReject()`: Menampilkan modal alasan penolakan untuk semua item terpilih. [DONE]
    *   Buat fungsi `handleCsvUpload()`: Mengirim file CSV ke backend. [DONE]

### 2. Perubahan Backend (Laravel - Controller & Service) [DONE]
*   **File:** `app/Http/Controllers/VerificationBudgetController.php` [DONE]
    *   Tambahkan method `bulkVerify(Request $request)`: Menerima array `item_ids`, `fix_prices` (optional), dan `notes`. [DONE]
    *   Tambahkan method `bulkReject(Request $request)`: Menerima array `item_ids` dan `notes`. [DONE]
    *   Tambahkan method `importCsv(Request $request)`: Menangani upload file CSV, parsing, dan memproses verifikasi berdasarkan `item_id` dan `price` di dalam file. [DONE]

*   **File:** `app/Services/VerificationBudgetService/VerificationBudgetService.php` (Interface) [DONE]
    *   Definisikan kontrak untuk `bulkVerify`, `bulkReject`, dan `processCsvImport`. [DONE]

*   **File:** `app/Services/VerificationBudgetService/VerificationBudgetServiceImpl.php` (Implementation) [DONE]
    *   Implementasikan logika bisnis di dalam `DB::transaction`. [DONE]
    *   Pastikan pengecekan otoritas (`canVerify`) dilakukan untuk setiap item. [DONE]
    *   Gunakan snapshoting data jika diperlukan sesuai aturan arsitektur. [DONE]

### 3. Penambahan Route [DONE]
*   **File:** `routes/web.php` [DONE]
    *   Tambahkan route berikut di dalam prefix `budget-verification`: [DONE]
        *   `POST /bulk-verify` -> `VerificationBudgetController@bulkVerify` [DONE]
        *   `POST /bulk-reject` -> `VerificationBudgetController@bulkReject` [DONE]
        *   `POST /import-csv` -> `VerificationBudgetController@importCsv` [DONE]

## Standar Teknis & Keamanan
1.  **Validation:** Gunakan `FormRequest` atau validasi ketat di Controller. [DONE]
2.  **Transaction:** Semua operasi bulk WAJIB dibungkus dalam `DB::beginTransaction()`. [DONE]
3.  **UI/UX:** Gunakan `SweetAlert2` untuk loading states dan konfirmasi. Jangan biarkan user melakukan aksi ganda saat proses sedang berjalan. [DONE]
4.  **Error Handling:** Tangkap `DomainException` dan berikan pesan error yang informatif jika ada satu item dalam batch yang gagal divalidasi. [DONE]
5.  **CSV Format:** Format CSV minimal harus mengandung kolom: `item_id`, `verified_price`. [DONE]

## Checklist Pengujian (Test Cases)
- [x] Verifikasi item terpilih berhasil (Bulk Approve).
- [x] Penolakan item terpilih berhasil (Bulk Reject).
- [x] Checkbox "Select All" berfungsi dengan benar.
- [x] Upload CSV dengan format salah memberikan pesan error yang jelas.
- [x] Upload CSV dengan format benar berhasil memperbarui `fix_price` dan mengubah status menjadi `verified`.
- [x] User yang tidak memiliki otoritas verifikasi ditolak oleh sistem (403 Forbidden).

---

# Issue: Implementasi Bulk Approval pada Tab Pending Approvals [DONE]

## Deskripsi Masalah
Sama halnya dengan fitur verifikasi harga, tab **Pending Approvals** pada halaman `budget-user` saat ini hanya mendukung proses approval/reject satu per satu. Untuk meningkatkan efisiensi pengguna dengan peran approver, diperlukan fitur untuk melakukan seleksi banyak item (checklist) dan melakukan approval atau reject secara masal.

## Rencana Implementasi

### 1. Perubahan UI (Frontend - Blade & JS) [DONE]
*   **File:** `resources/views/pages/budget/budget-user.blade.php` [DONE]
    *   Tambahkan kolom Checkbox di sebelah kiri tabel `#approvalItemsTable`. [DONE]
    *   Tambahkan checkbox "Select All" di header tabel approval. [DONE]
    *   Tambahkan Toolbar di atas tabel approval yang berisi:
        *   Button `Bulk Approve` (Muncul saat ada item terpilih). [DONE]
        *   Button `Bulk Reject` (Muncul saat ada item terpilih). [DONE]
    *   Gunakan modal yang sudah ada (`rejectCommentModal`) atau buat baru untuk bulk rejection guna menampung komentar penolakan masal. [DONE]

*   **Logic Script (Blade):** [DONE]
    *   Update fungsi `renderPendingApprovalItems(items)` untuk menyertakan checkbox. [DONE]
    *   Buat event listener untuk checkbox approval (individual & select all). [DONE]
    *   Buat fungsi `handleBulkApprove()`: Mengumpulkan `detail_ids` yang dipilih dan mengirim ke backend. [DONE]
    *   Buat fungsi `handleBulkReject()`: Menampilkan modal alasan penolakan untuk semua item terpilih. [DONE]

### 2. Perubahan Backend (Laravel - Controller & Service) [DONE]
*   **File:** `app/Http/Controllers/WorkplanBudgetItemMasterApprovalController.php` [DONE]
    *   Tambahkan method `bulkProcess(Request $request)`: Menerima array `detail_ids`, `action` (approve/reject), dan `comments`. [DONE]

*   **File:** `app/Services/WorkplanBudgetItemApprovalService.php` [DONE]
    *   Tambahkan method `bulkProcessApproval(array $detailIds, string $action, int $approverId, ?string $comments = null)`: [DONE]
        *   Gunakan `DB::transaction`. [DONE]
        *   Iterasi `detail_ids` dan panggil `processApproval` untuk setiap item. [DONE]
        *   Kumpulkan hasil sukses/gagal untuk dikembalikan ke controller. [DONE]

### 3. Penambahan Route [DONE]
*   **File:** `routes/web.php` [DONE]
    *   Tambahkan route berikut di dalam prefix `workplan-budget-item-approval`: [DONE]
        *   `POST /bulk-process` -> `WorkplanBudgetItemMasterApprovalController@bulkProcess` [DONE]

## Checklist Pengujian (Test Cases)
- [x] Checklist "Select All" pada tab approval berfungsi dengan benar.
- [x] Bulk Approve item terpilih berhasil memajukan status ke level berikutnya atau menjadi `approved`.
- [x] Bulk Reject item terpilih berhasil mengubah status menjadi `rejected` dan meminta komentar wajib.
- [x] Validasi sekuensial tetap berjalan (tidak bisa menyetujui item yang belum gilirannya, meskipun terpilih).
- [x] Notifikasi dikirimkan ke approver berikutnya atau requester setelah proses bulk selesai.


