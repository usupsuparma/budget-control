# Planning Implementation: Import Workplan & Budget Items dari CSV

## 1. Analisis & Tujuan Fitur
Fitur ini bertujuan untuk menyediakan fungsionalitas impor data melalui file CSV untuk mengisi target Key Performance Indicator (KPI) pada tabel `kpi_workplans` beserta detail anggarannya pada tabel `workplan_budget_items`.

Asumsi utama: Data yang berhasil di-import ini adalah *initial state* (data awal) yang berarti **sudah siap menuju ke proses transaksi** pada tabel `transactions` (`Transaction.php`). 

Dikarenakan proses ini bersifat modifikasi skala besar (*bulk insert/update*) dan kritikal, implementasi **DIWAJIBKAN** untuk mematuhi arsitektur **Service Layer** sesuai aturan pada `GEMINI.md`. Dilarang menempatkan logika bisnis di dalam Controller.

### Format Struktur CSV
Baris header (Baris 1 & Baris 2) berformat kurang lebih sebagai berikut:
```csv
CODE;NAME;ACTIVEFLAG;INCHARGECODE;REMARKS;Goods Code;AC Code;Price;1.JAN;;2.FEB;;3.MAR;;4.APR;;5.MAY;;6.JUN;;7.JUL;;8.AUG;;9.SEP;;10.OCT;;11.NOV;;12.DEC;
;;;;;;;;Qty;Amount;Qty;Amount;Qty;Amount;Qty;Amount;Qty;Amount;Qty;Amount;Qty;Amount;Qty;Amount;Qty;Amount;Qty;Amount;Qty;Amount;Qty;Amount
```

---

## 2. Rencana Eksekusi (Langkah demi Langkah)

### A. Routing & Validasi Upload (Controller Layer)
1. **Penambahan Route (`routes/web.php`)**:
   Buat sebuah route `POST` baru untuk fungsionalitas upload CSV.
   ```php
   Route::post('/import/workplan-budget', [ImportController::class, 'import'])->name('import.workplan-budget');
   ```
2. **Form Request (Mandatory)**:
   Gunakan artisan `make:request` (misal: `ImportCsvRequest`) untuk memvalidasi *request*. Pastikan file yang diunggah valid (mimes:`csv,txt` dan ukurannya sesuai). Controller hanya bertugas memanggil validasi array ini lalu pass payload ke Service Layer.

### B. Pembuatan Service Layer Baru
1. Buat interface, misal `WorkplanImportService.php` dan implementasinya `WorkplanImportServiceImpl.php`.
2. Jangan lupa daftarkan binding Service ini ke dalam `app/Providers/CustomServiceProvider.php`.
3. Seluruh logika dari pembacaan CSV, *looping* validasi logic per baris, dan penyisipan data dibungkus dalam blok `DB::transaction()` di dalam class service ini.

### C. Proses Bisnis & Pemetaan Data (Core Logic dalam Service)
Saat melakukan proses pembacaan per baris di dalam skrip, terapkan secara berurutan langkah bisnis berikut:

1. **Filter ACTIVEFLAG**:
   Cek nilai pada kolom `ACTIVEFLAG`. Jika nilainya adalah `0`, maka abaikan baris tersebut (`continue`). Hanya proses baris dengan `ACTIVEFLAG == 1`.

2. **Filter Harga (Price)**:
   Cek pada kolom `Price`. Jika nilainya `< 1` (kurang dari 1), maka skip baris tersebut. Proses hanya jika `Price >= 1`.

3. **Resolusi INCHARGECODE & Hierarki Org**:
   Cocokkan nilai pada kolom `INCHARGECODE` dengan kolom `code` di tabel organisasi secara berurutan: `sections`, lalu `departments`, lalu `divisions`. 
   Identifikasi baris CSV ini masuk ke divisi, departemen, atau section mana berdasarkan kecocokan kode tersebut.

4. **Pencarian & Pembuatan `kpi_workplans` (Auto-Generate)**:
   Setelah menelusuri code organisasi berdasarkan `INCHARGECODE`, cari KPI Workplan (*kpi_type* 'department' atau 'section') yang sesuai:
   - Jika KPI berdasar referensi di atas **sudah ada**, gunakan ID KPI tersebut.
   - Jika **belum ada**, **GENERATE** data KPI baru pada tabel `kpi_workplans`.
   - **Aturan Penting saat Generate KPI**: Pembuatan harus dirut mengacu pada hirarkinya (`divisi -> departement -> section`). Pastikan parent dari setiap KPI terbentuk secara rekursif jika belum ada.

5. **Pengisian Item Anggaran (`workplan_budget_items`)**:
   Isi data anggaran ini dengan merelasikannya ke target KPI dari Langkah 4 (`kpi_workplan_id`).
   - `budget_code`       : Diisi dari kolom **`CODE`** pada CSV. Kode ini merupakan referensi `budget_code` pada tabel `budget_code`.
   - `price_estimation`  : Diisi persis dari kolom `Price` CSV.
   - `price_final`       : Diisi persis dari kolom `Price` CSV.
   - Kuantitas Kegiatan (`activity_jan`, `activity_feb`, ..., `activity_dec`): Lakukan sinkronisasi ke array offset CSV, dan ekstrak nilainya spesifik pada letak *sub-header* **Qty** pada bulan bersangkutan secara tepat.

---

## 3. Checklist Verifikasi & QA (Panduan untuk Dev/Agent)
- [ ] Route web `/import/workplan-budget` telah ditambahkan di `routes/web.php`.
- [ ] Controller murni hanya memanggil method pada Service, tidak ada `DB::transaction` atau pemanggilan Models secara langsung.
- [ ] Terdapat `FormRequest` khusus yang handle proteksi _file input_ CSV.
- [ ] Proses *Skip Row* terjadi dengan benar ketika `ACTIVEFLAG == 0` atau `Price < 1`.
- [ ] Kolom `CODE` dari file CSV berhasil dipetakan menjadi `budget_code` di `workplan_budget_items`.
- [ ] Identifikasi organisasi berdasarkan `INCHARGECODE` bekerja dengan baik (mencocokkan code di Section > Dept > Divisi).
- [ ] Auto-generate `kpi_workplans` berjalan mulus dengan mengedepankan pembentukan struktur hirearkinya terlebih dahulu (divisi->dept->section) jika belum eksis.
- [ ] Nilai *Price* masuk dengan tervalidasi ke field `price_estimation` & `price_final` berserta parsing data `Qty` dari bulan Jan - Dec yang tepat.
- [ ] Terkondisikan dengan baik hingga state nya siap menuju prosedur Transaksi.
- [ ] Seluruh skenario telah dibuktikan melalui pembuatan **Unit/Feature Test (Pest/PHPUnit)**.

> **CATATAN PENTING**: Jangan pernah melakukan penebakan (*guesswork*) mengenai asumsi yang berhubungan dengan referensi tabel. Apabila menjumpai ketidaksesuaian/ambiguitas ketika melakukan *data-mapping* (seperti ke *Transaction status*), **mohon tanyakan dan minta konfirmasi terlebih dahulu!**
