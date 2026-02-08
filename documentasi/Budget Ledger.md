Berikut adalah draf **Dokumentasi Teknis & Panduan Implementasi** untuk Modul Kontrol Anggaran (Budget Ledger System). Dokumentasi ini dirancang agar Anda bisa langsung menerjemahkannya ke dalam kode *backend* (seperti Controllers, Services, atau Observers di *framework* pilihan Anda).

---

# 📚 Dokumentasi Sistem Kontrol Anggaran (Budget Ledger)

## 1. Konsep Dasar Sistem

Sistem ini menggunakan pendekatan **Ledger (Buku Besar)** untuk melacak sisa anggaran. Nilai pagu awal di master data (`workplan_budget_items`) bersifat statis dan **tidak boleh diubah (di-update)** oleh transaksi harian. Seluruh pergerakan uang keluar (Cash Advance) dan uang kembali (LPJ Refund) dicatat sebagai mutasi baru di tabel `budget_mutations`.

**Rumus Utama Saldo Real-time:**


## 2. Struktur Database (Entity Relationships)

Sistem ini melibatkan 4 tabel utama:

1. **`workplan_budget_items`**: Master data pagu anggaran (Pagu Awal).
2. **`transactions` & `transaction_details**`: Dokumen pengajuan pencairan dana (Cash Advance).
3. **`transaction_lpj_submissions`**: Dokumen pelaporan penggunaan dana (LPJ).
4. **`budget_mutations`** *(Tabel Baru)*: Buku besar yang merekam setiap pemotongan atau pengembalian dana ke `workplan_budget_items`.

### DDL Tabel `budget_mutations`

```sql
CREATE TABLE budget_mutations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workplan_budget_item_id BIGINT UNSIGNED NOT NULL,
    transaction_id BIGINT UNSIGNED NOT NULL,
    transaction_detail_id BIGINT UNSIGNED NOT NULL,
    transaction_lpj_submission_id BIGINT UNSIGNED NULL,
    
    mutation_type ENUM('D', 'C') NOT NULL COMMENT 'D=Debit (Keluar), C=Credit (Masuk/Refund)',
    amount DECIMAL(19, 4) NOT NULL DEFAULT 0.0000,
    category VARCHAR(50) NOT NULL COMMENT 'CASH_ADVANCE, LPJ_REFUND, LPJ_REIMBURSE',
    description TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (workplan_budget_item_id) REFERENCES workplan_budget_items(id),
    FOREIGN KEY (transaction_id) REFERENCES transactions(id),
    FOREIGN KEY (transaction_detail_id) REFERENCES transaction_details(id),
    FOREIGN KEY (transaction_lpj_submission_id) REFERENCES transaction_lpj_submissions(id)
);

```

---

## 3. Alur Implementasi Logic (SOP Backend)

Ini adalah alur logika yang harus Anda tulis di *backend service* saat terjadi perubahan status dokumen. **Sangat disarankan membungkus logika ini dalam Database Transaction (`DB::beginTransaction()`)** untuk mencegah data *corrupt* jika terjadi *error* di tengah proses.

### Fase 1: Approval Transaksi (Cash Advance)

**Kondisi (Trigger):** Dokumen `transactions` di-Approve oleh atasan.
**Tindakan Sistem:**

1. *Looping* semua data di `transaction_details` milik `transaction_id` tersebut.
2. Lakukan *insert* ke tabel `budget_mutations` untuk memotong anggaran.
3. **Data yang di-insert:**
* `mutation_type` = **'D'** (Debit)
* `amount` = Nilai dari `transaction_details.estimated_total`
* `category` = 'CASH_ADVANCE'
* `transaction_lpj_submission_id` = `NULL`



### Fase 2: Input LPJ oleh User

**Kondisi (Trigger):** User membuat LPJ baru.
**Tindakan Sistem:**

1. Buat *record* baru di `transaction_lpj_submissions`.
2. *Update* tabel `transaction_details`, isi kolom `fix_total` dengan nominal kuitansi asli dari lapangan.
3. *(Saldo anggaran belum berubah di fase ini).*

### Fase 3: Approval LPJ (Settlement)

**Kondisi (Trigger):** Dokumen LPJ di-Approve oleh bagian Keuangan.
**Tindakan Sistem:**
Sistem harus menghitung selisih antara uang yang dibawa (Estimasi) dan uang yang dipakai (Fix).

*Pseudocode Logic:*

```php
foreach ($transaction->details as $detail) {
    $uang_dibawa = $detail->estimated_total;
    $uang_terpakai = $detail->fix_total;
    
    $selisih = $uang_dibawa - $uang_terpakai;
    
    if ($selisih > 0) {
        // KASUS 1: Uang sisa (Hemat). Harus dikembalikan ke budget.
        // Insert Credit ke budget_mutations
        insertMutation([
            'type' => 'C', 
            'amount' => abs($selisih), 
            'category' => 'LPJ_REFUND'
        ]);
    } 
    elseif ($selisih < 0) {
        // KASUS 2: Uang kurang (Overbudget). Budget dipotong lagi untuk reimburse ke user.
        // Insert Debit ke budget_mutations
        insertMutation([
            'type' => 'D', 
            'amount' => abs($selisih), 
            'category' => 'LPJ_REIMBURSE'
        ]);
    }
    // Jika $selisih == 0 (Pas), tidak perlu insert mutasi baru.
}

```

---

## 4. Query Standardisasi Data (Data Access)

Gunakan *Query* ini di *Model* atau *Repository* Anda untuk selalu mendapatkan nilai pagu yang valid (misalnya saat menampilkan *Dashboard* atau memvalidasi apakah saldo cukup sebelum user *submit* transaksi baru).

```sql
SELECT 
    wbi.id,
    wbi.description,
    CAST(wbi.total AS DECIMAL(15,2)) AS initial_budget,
    
    -- Hitung total pemakaian (Debit)
    COALESCE(SUM(CASE WHEN bm.mutation_type = 'D' THEN bm.amount ELSE 0 END), 0) AS total_debit,
    
    -- Hitung total pengembalian (Credit)
    COALESCE(SUM(CASE WHEN bm.mutation_type = 'C' THEN bm.amount ELSE 0 END), 0) AS total_credit,
    
    -- Hitung sisa saldo
    (
        CAST(wbi.total AS DECIMAL(15,2)) 
        - COALESCE(SUM(CASE WHEN bm.mutation_type = 'D' THEN bm.amount ELSE 0 END), 0)
        + COALESCE(SUM(CASE WHEN bm.mutation_type = 'C' THEN bm.amount ELSE 0 END), 0)
    ) AS current_balance

FROM workplan_budget_items wbi
LEFT JOIN budget_mutations bm ON wbi.id = bm.workplan_budget_item_id
WHERE wbi.id = ? -- Masukkan ID Budget spesifik jika perlu
GROUP BY wbi.id, wbi.description, wbi.total;

```

## 5. ⚠️ Aturan Emas (Golden Rules) Implementasi

1. **Immutability:** Data mutasi yang sudah ter-insert di `budget_mutations` sifatnya *Read-Only*. Jika ada salah input, JANGAN `UPDATE` baris mutasi tersebut. Buatlah mutasi baru (Adjustment) untuk mengoreksi nilainya. Ini adalah prinsip dasar *audit trail*.
2. **Validasi Saldo:** Sebelum mengeksekusi Fase 1 (Approval Transaksi), pastikan *backend* Anda mengecek: *Apakah `estimated_total` <= `current_balance`?* Jika tidak, tolak approval (karena *overbudget*).
3. **Tipe Data Uang:** Selalu pastikan variabel penampung nominal uang di sisi kode *backend* Anda tidak mengalami kehilangan presisi *floating point*.

---

Apakah Anda ingin saya membuatkan contoh *syntax* spesifik menggunakan fitur Eloquent / Query Builder Laravel untuk meng-handle proses *insert* DB Transaction pada Fase 3 di atas?