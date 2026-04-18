# Issue: Implementasi Status 0 dan Trigger Approval Process pada Import User Submission

## Deskripsi Masalah
Saat ini pada menu User Submission (`admission/user`), fitur Import data dari file Excel/CSV belum secara sepenuhnya menjamin pengajuan yang diimport memiliki status awal `0` (Submission/Pending) dan terintegrasi dengan mulus pada proses dua-fase *dynamic approval system* (Uppline Chain -> Master Flow). Pengguna ingin agar proses *approve* berjalan persis sama dengan fitur "Add data" (manual form).

## Rencana Implementasi

### 1. Perubahan & Verifikasi Backend (Service Layer) [DONE]
*   **File:** `app/Services/SubmissionService/SubmissionServiceImpl.php`
    *   **Method `importTransactions($file)`:**
        Walaupun saat ini sudah memanggil `$this->createTransaction($transactionData)`, pastikan variabel relasi (*urgency*, tipe program) terbaca presisi dari *excel parsing* agar aturan *threshold-based routing* atau jenis approval yang di-hit tidak keliru (misalnya, jika *auto_approve* tersetel nyala saat tidak terencana).
    *   **Method `createTransaction($data)`:**
        Pastikan nilai field statu secara eksplisit terkunci di nilai `Transaction::STATUS_PENDING` (yaitu `0`) saat awal transaksi disimpan ke DB.
    *   **Triggering Approval Workflow:**
        Pastikan pemanggilan rutin terhadap `$this->approvalTransactionService->submitForApproval($transaction->id)` berhasil membuat `ApprovalRequest` dan rantai `ApprovalRequestDetail`. Tangkap *exception* apa pun (seperti `DomainException`) yang muncul jika *uppline_id* dari uploader bersangkutan ternyata `null` atau `chain` approval tidak tersedia.

### 2. Validasi & Penanganan Error Mode Batch [DONE]
*   **Exception & Atomicity:**
        Jika di tengah baris excel terjadi kesalahan (misal approver putus), maka implementasikan pembatalan (rollback) via `DB::transaction`. Jika bisnis memperbolehkan parsial (baris gagal di-skip, baris valid dilanjutkan), kumpulkan error tersebut ke variabel dan cetak sebagai catatan tanpa membongkar keseluruhan flow. 
        **Perhatian!** Standard project ini biasanya preferensi pada atomisasi yang jelas, di mana seluruh satu blok *row group* yang invalid men-trigger *rollback*.
*   **File Controller:** Dalam `SubmissionController@import`, tangkap *response* pengecekan dari service apabila pengajuan approval gagal di generate akibat konfigurasi hirarki karyawan yang salah, dan lemparkan status kode 422 agar frontend memprosesnya.

### 3. Perubahan UI (Frontend - Action Feedback) [DONE]
*   **File:** `resources/views/pages/submission/user.blade.php` (Blade & Javascript DOM)
    *   Sesuaikan pesan saat proses unggah (AJAX File Upload) usai. Tampilkan detail respons secara jelas, contoh: *"Berhasil mengimpor X transaksi dan mengajukannya ke dalam proses Approval."* (Lebih baik memakai SweetAlert2 `Swal.fire`).
    *   Pastikan tabel otomatis ter-*reload* via data-driven js dan item baru tersebut muncul dengan *Badge* status "Pending" atau "Submission".

## Standar Teknis & Keamanan (Mematuhi `GEMINI.md`)
1.  **NO Model queries/CRUD in Controllers.** - Tetap berada di Service `$this->submissionService->importTransactions`.
2.  **Zero-Test Tolerance:** Sertakan skenario pengujian di Pest/PHPUnit saat memverifikasi API import, untuk dipastikan tidak memunculkan N+1 query.
3.  **Data-Driven Updates:** Pada `onSuccess` callback di Javascript, cukup panggil API untuk *fetch table* terbaru, jangan *append DOM* manual (hindari penyimpanan *business data* di atribut `data-*`).

## Checklist Pengujian (Test Cases)
- [x] Upload *Template* CSV yg memiliki *Budget Code* & *Program* benar, transaksi harus tampil di datatable dengan status 0 (Submission/Pending).
- [x] Pengajuan yang masuk via import sukses mendistribusikan notifikasi/pending state ke akun atasan (*approver* level pertama).
- [x] Jika struktur hirarki atasan uploader putus, validasi menangkapnya sebelum file berhasil di-import (mencegah *orphan submission* berstatus 0).
- [x] File dengan formasi *budget limit* yang membeludak, segera menghentikan import sebelum tersimpan.
