Tentu, ini adalah **Dokumentasi Teknis Final (Versi Pure Ledger)** yang telah diperbarui sesuai dengan diskusi terakhir kita (di mana Saldo Awal juga masuk ke dalam tabel mutasi).

Dokumentasi ini menggantikan versi sebelumnya.

---

# 📘 Dokumentasi Teknis: Budget Control System (Pure Ledger)

## 1. Filosofi Sistem

Sistem ini menggunakan pendekatan **Pure Ledger**. Artinya, tabel `budget_mutations` adalah satu-satunya sumber kebenaran (*Single Source of Truth*) untuk nilai saldo.

* Kami **TIDAK** menggunakan kolom `total` di tabel master untuk perhitungan saldo berjalan.
* **Saldo Awal** dicatat sebagai transaksi "Credit" pertama di tabel mutasi.
* **Rumus Saldo:** `Total Credit (Masuk) - Total Debit (Keluar)`.

## 2. Struktur Database (Schema Update)

Perubahan utama ada pada kolom referensi transaksi yang kini bersifat **NULLABLE**. Ini wajib karena transaksi "Saldo Awal" atau "Top Up Anggaran" tidak memiliki ID Transaksi belanja.

### DDL Tabel `budget_mutations` (Final)

```sql
CREATE TABLE budget_mutations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- 1. SUMBER ANGGARAN (Wajib)
    workplan_budget_item_id BIGINT UNSIGNED NOT NULL,
    
    -- 2. REFERENSI DOKUMEN (Nullable)
    -- Boleh NULL jika tipe transaksinya adalah INITIAL_BUDGET atau ADJUSTMENT
    transaction_id BIGINT UNSIGNED NULL,
    transaction_detail_id BIGINT UNSIGNED NULL,
    transaction_lpj_submission_id BIGINT UNSIGNED NULL,
    budget_submission_id BIGINT UNSIGNED NULL,
    
    -- 3. INTI LEDGER
    mutation_type ENUM('D', 'C') NOT NULL COMMENT 'D=Debit (Keluar/Penggunaan), C=Credit (Masuk/Refund/Initial)',
    amount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
    
    -- 4. METADATA
    category VARCHAR(50) NOT NULL COMMENT 'INITIAL_BUDGET, CASH_ADVANCE, LPJ_REFUND, LPJ_REIMBURSE, BUDGET_AMENDMENT, BUDGET_RELOCATION_OUT, BUDGET_RELOCATION_IN',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- 5. RELASI
    FOREIGN KEY (workplan_budget_item_id) REFERENCES workplan_budget_items(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_detail_id) REFERENCES transaction_details(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_lpj_submission_id) REFERENCES transaction_lpj_submissions(id) ON DELETE SET NULL,
    FOREIGN KEY (budget_submission_id) REFERENCES budget_submissions(id) ON DELETE SET NULL,
    
    -- Index untuk performa hitung saldo
    INDEX idx_ledger_calc (workplan_budget_item_id, mutation_type)
);

```

---

## 3. Alur Logika Bisnis (Backend Flow)

Berikut adalah *trigger points* di mana Anda harus melakukan insert ke `budget_mutations`.

### Fase 0: Inisialisasi Anggaran (PENTING)

**Trigger:** Dokumen Rencana Kerja (`kpi_workplans`) disetujui (Status: **Approved**).
**Aksi:** Masukkan saldo awal ke ledger.

```sql
-- Contoh Query Insert
INSERT INTO budget_mutations (
    workplan_budget_item_id, mutation_type, amount, category, description
) VALUES (
    1, 'C', 100000000, 'INITIAL_BUDGET', 'Saldo Awal Disetujui'
);

```

### Fase 1: Pencairan Dana (Cash Advance)

**Trigger:** Dokumen `transactions` disetujui (Status: **Approved**).
**Aksi:** Catat pengeluaran uang (Debit).

1. Ambil semua `transaction_details`.
2. Looping dan insert ke ledger:
* `mutation_type`: **'D'** (Debit)
* `amount`: `detail.estimated_total`
* `category`: 'CASH_ADVANCE'
* `transaction_id`: (ID Transaksi)
* `transaction_detail_id`: (ID Detail)



### Fase 2: Pelaporan LPJ (Settlement)

**Trigger:** Dokumen `transaction_lpj_submissions` disetujui (Status: **Approved**).
**Aksi:** Bandingkan Estimasi vs Realisasi, lalu catat selisihnya.

*Pseudocode Logic:*

```php
$details = TransactionDetail::where('transaction_id', $id_transaksi)->get();

foreach ($details as $detail) {
    // Hitung selisih
    $uang_diambil = $detail->estimated_total;
    $uang_terpakai = $detail->fix_total; // Dari inputan LPJ user
    $selisih = $uang_diambil - $uang_terpakai;

    if ($selisih > 0) {
        // CASE: HEMAT (Uang Sisa) -> Credit (Kembalikan ke Saldo)
        createMutation(
            type: 'C',
            amount: abs($selisih),
            category: 'LPJ_REFUND',
            lpj_id: $lpj_id,
            detail_id: $detail->id
        );
    } elseif ($selisih < 0) {
        // CASE: TEKOR (Kurang Bayar) -> Debit (Keluarkan lagi dari Saldo)
        createMutation(
            type: 'D',
            amount: abs($selisih),
            category: 'LPJ_REIMBURSE',
            lpj_id: $lpj_id,
            detail_id: $detail->id
        );
    }
}

```

### Fase Tambahan: Budget Movement / Revisi Anggaran

**Trigger:** Dokumen `budget_submissions` disetujui penuh melalui approval chain.

Budget movement memiliki dua tipe:

1. **Add Budget**
   * `budget_submissions.budget_account_id` menunjuk ke `workplan_budget_items.id` tujuan.
   * Pada approval terakhir, approver wajib memilih `budget_submissions.source_budget_account_id` melalui filter divisi, workplan, dan budget account sumber.
   * Saat approved, insert dua mutasi dalam satu transaksi database:
     * Sumber: `mutation_type`: **'D'**
     * `category`: **'BUDGET_AMENDMENT'**
     * `amount`: `budget_submissions.approved_amount` jika terisi, fallback ke `budget_submissions.estimation_amount`
     * `budget_submission_id`: ID submission
     * Tujuan: `mutation_type`: **'C'**
     * `category`: **'BUDGET_AMENDMENT'**
     * `amount`: `budget_submissions.approved_amount` jika terisi, fallback ke `budget_submissions.estimation_amount`
     * `budget_submission_id`: ID submission
   * Source dan target tidak boleh sama.
   * Source wajib merupakan budget item approved dari workplan sumber yang dipilih approver.
   * Saldo source wajib cukup berdasarkan rumus pure ledger sebelum mutasi dicatat.
   * Jika approver final mengubah nominal approved, `approved_amount_changed_by` dan `approved_amount_changed_at` menjadi audit perubahan.

2. **Relocation Budget**
   * `budget_submissions.source_budget_account_id` menunjuk ke `workplan_budget_items.id` sumber.
   * `budget_submissions.budget_account_id` menunjuk ke `workplan_budget_items.id` tujuan.
   * Saat approved, insert dua mutasi dalam satu transaksi database:
     * Sumber: `mutation_type` **'D'**, `category` **'BUDGET_RELOCATION_OUT'**
     * Tujuan: `mutation_type` **'C'**, `category` **'BUDGET_RELOCATION_IN'**
   * Source dan target tidak boleh sama.
   * Source dan target wajib merupakan budget item approved dari workplan yang dipilih pada submission.
   * Saldo source wajib cukup berdasarkan rumus pure ledger sebelum mutasi dicatat.

`budget_submission_id` digunakan sebagai referensi idempotency. Jika approval final terpanggil ulang, service tidak boleh membuat mutasi budget movement ganda.

**Catatan UI:** Field `Estimation` pada form Budget Movement ditampilkan dengan separator ribuan format Indonesia (contoh `1.000.000`) untuk mengurangi salah input. Nilai yang dikirim ke backend tetap angka mentah tanpa separator (contoh `1000000`) agar validasi numeric dan pencatatan ledger tetap konsisten.

Pada daftar Budget Movement, status `In Approval Process` dapat diklik untuk membuka modal progress approval. Modal ini menampilkan nomor approval request, posisi level saat ini, approver yang sedang menunggu aksi, dan semua approver pada timeline beserta statusnya.

### Fase Tambahan Lama: Top Up Anggaran Manual (Optional)

Jika di masa depan ada penambahan anggaran (*Top Up*), Anda cukup insert baris baru:

* `mutation_type`: **'C'**
* `category`: 'BUDGET_AMENDMENT'
* `amount`: (Nilai Top Up)

---

## 4. Query Monitoring Saldo (Data Access)

Gunakan query ini untuk Dashboard atau pengecekan saldo sebelum transaksi. Perhatikan bahwa kita tidak lagi menggunakan `wbi.total` dalam rumus aritmatika, hanya sebagai info display saja.

```sql
SELECT 
    wbi.id,
    wbi.budget_code,
    wbi.description,
    
    -- Info Pagu Awal (Hanya untuk display/perbandingan)
    CAST(wbi.total AS DECIMAL(19,4)) AS original_pagu_plan,
    
    -- 1. TOTAL CREDIT (Pemasukan: Initial + Refund + TopUp)
    COALESCE(SUM(CASE WHEN bm.mutation_type = 'C' THEN bm.amount ELSE 0 END), 0) AS total_in,
    
    -- 2. TOTAL DEBIT (Pengeluaran: Cash Advance + Reimburse)
    COALESCE(SUM(CASE WHEN bm.mutation_type = 'D' THEN bm.amount ELSE 0 END), 0) AS total_out,
    
    -- 3. SISA SALDO (Total In - Total Out)
    (
        COALESCE(SUM(CASE WHEN bm.mutation_type = 'C' THEN bm.amount ELSE 0 END), 0) - 
        COALESCE(SUM(CASE WHEN bm.mutation_type = 'D' THEN bm.amount ELSE 0 END), 0)
    ) AS current_balance

FROM workplan_budget_items wbi
LEFT JOIN budget_mutations bm ON wbi.id = bm.workplan_budget_item_id
GROUP BY wbi.id, wbi.budget_code, wbi.description, wbi.total;

```

---

## 5. Golden Rules Implementasi

1. **Strict Append-Only:** Tabel `budget_mutations` dirancang untuk mencatat sejarah. Jangan pernah melakukan `UPDATE` atau `DELETE` pada data yang sudah lama tersimpan (kecuali *soft delete* seluruh transaksi jika dibatalkan). Jika ada kesalahan angka, buatlah mutasi penyeimbang baru (Adjustment).
2. **Validasi Saldo:** Sebelum `Phase 1` (Approval Transaksi), jalankan Query Monitoring Saldo di atas.
* *Rule:* `Request Amount` harus <= `current_balance`.


3. **Handling NULL:** Pastikan kode backend Anda siap menangani nilai `NULL` pada kolom `transaction_id` saat membaca data dengan kategori `INITIAL_BUDGET`.
