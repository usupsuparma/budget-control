# Integration Plan: MacframeGA Excel Import for User Submission

## Goal Description
Menambahkan fungsionalitas import data untuk User Submission dengan format baru yang di-generate dari aplikasi `MacframeGA`. Proses ini harus memfasilitasi parsing file Excel dengan struktur khusus (Header Master di row 1, Data Master di row 2, Header Detail di row 3, dan Data Detail mulai dari row 4). Setelah data berhasil di-parse dan divalidasi oleh backend, aplikasi harus menampilkan modal kepada user berisi preview data dan pilihan `Program ID` (KPI Workplan) yang akan di-bind ke seluruh baris data tersebut sebelum di-simpan secara permanen ke database.

## Architecture & Workflows (Sesuai GEMINI.md)
1. **Service Layer Pattern**: Logika parsing Excel dan import/penyimpanan wajib diletakan di `SubmissionServiceImpl`. Controller hanya bertugas melempar data.
2. **Two-Phase Upload Method**:
   - **Phase 1 (Parsing & Preview)**: Frontend mengirimkan file Excel. Backend mem-parsing file, melakukan standarisasi (mapping), lalu mereturn data preview berbasis JSON ke Frontend.
   - **Phase 2 (Mapping Program ID & Commit)**: Frontend menampilkan modal preview hasil parsing beserta Dropdown pencarian *Program*. Setelah user memilih Program dan menekan Konfirmasi, Frontend mengirimkan payload JSON beserta `program_id` ke Backend untuk di-store ke database dalam bentuk `DB::transaction(fn() => ...)`.

---

## Proposed Changes

### 1. [MODIFY] `resources/views/pages/submission/user.blade.php`
**Perubahan UI/UX untuk Modal Import dan Preview:**
- Update `importModal` yang ada saat ini untuk memiliki jenis tipe import (Radio/Select): "Template System" atau "Format MacframeGA".
- Tambahkan JavaScript AJAX handler untuk Form Import Macframe: Jika user mengupload format Macframe, file dilempar ke AJAX route `review-import` tanpa menyimpan ke DB dulu.
- Buat Modal Baru (`importPreviewModal`) yang akan memunculkan:
  - Table preview row-row data dari Macframe yang akan diimport (Date, Item, QTY, Price, dll).
  - Component select `Program ID` yang wajib menggunakan plugin *Choices.js* (individual instance initialization). 
  - Button "Konfirmasi Import" untuk mensubmit *Payload Data* beserta `Program ID` ke endpoit import final.

### 2. [MODIFY] `routes/web.php`
**Penambahan routes baru pada prefix `admission/user`:**
- `POST /admission/user/import-macframe-preview` : Endpoint untuk meng-upload dan parse file Macframe (tanpa save DB).
- `POST /admission/user/import-macframe-commit` : Endpoint untuk memfinalisasi input payload dan `program_id` ke dalam Database.

### 3. [MODIFY] `app/Http/Controllers/SubmissionController.php`
**Penambahan Method Controller:**
- `previewMacframeImport(Request $request)` : Menerima upload file Excel. Memanggil service untuk parsing struktur Macframe, lalu mereturn data array/JSON format preview ke Controller. Memerlukan FormRequest validasi type `.xlsx`/`.csv`.
- `commitMacframeImport(Request $request)` : Menerima array list request preview yang sudah di-mapping dan param `program_id`. Divalidasi dan di passing ke Service Layer `commitMacframe`.

### 4. [MODIFY] `app/Services/SubmissionService/SubmissionService.php`
**Deklarasi Method Interface:**
- `parseMacframeFile(UploadedFile $file): array`
- `commitMacframeTransactions(array $parsedData, int $programId): array`

### 5. [MODIFY] `app/Services/SubmissionService/SubmissionServiceImpl.php`
**Implementasi Business Logic:**
- **Membaca Excel**: Menggunakan library PhpSpreadsheet (atau Maatwebsite Excel).
  - Spesifik row reading:
    - Row 2 = Master Data (Tarik `Date` index ke-1 dari array untuk mapping `Transaction Date`).
    - Row 4 dst = Detail Data. Loop data ini.
  - Mapping ke field database:
    - Macframe `Goods/Charges` -> BC `goods_service_name` (nama item)
    - Macframe `D Descript` -> BC `purpose` (Tujuan)
    - Macframe `D Descript(contents)` -> BC `urgency` (Urgensi)
    - Macframe `Unit` -> BC `unit_id` (Cari/Translate ID unit berdasarkan nama).
    - Macframe `QTY` -> BC `quantity`
    - Macframe `Price` -> BC `price`
- **Penyimpanan (commitMacframeTransactions)**:
  - Eksekusi menggunakan parameter `DB::transaction(fn() => ... )`.
  - Merubah status transaksinya menjadi "0" (Pending) sesuai *Dynamic Approval System Workflow*.
  - Pembuatan *Immutable Snapshots* untuk data workflow.

---

## Technical Details / Tips for Implementer
- **Date Conversion**: Data `Date` dari excel Macframe biasanya berupa serial format date. Pastikan dikonversi menjadi string tanggal Y-m-d standard (`PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject`).
- **Tantangan Validasi Unit**: Pastikan String `Unit` dari Macframe (ex: "PCS", "KG") diparsing dan dibaca ke tabel `units` di Budget Control untuk mendapatkan `unit_id`. Tangani skenario jika unit tidak ditemukan (fallback error atau pembuatan otomatis - lebih disarankan set fallback / custom validation exception `DomainException`).
- **Choices.js**: Sesuai dengan GEMINI.md, modal `Program ID` wajib pakai `Choices.js`.
- **Eager Loading**: Untuk menghindari lazy load, saat mengembalikan data response (jika dibutuhkan) cantumkan logic `->with()`.

## Verification Plan
1. Siapkan file dummy `MacframeGA` (`format.xlsx`).
2. Login dan masuk ke menu list pengajuan -> klick button **Import**.
3. Pilih mode import Macframe, drop `format.xlsx` -> click import.
4. Akan muncul **Modal Preview** memunculkan list isian Excel dan meminta input Program ID. Data UI pastikan cocok dengan preview file asli.
5. Select Program ID dengan *Choices.js*. Click konfirmasi.
6. Notifikasi Swal `Sukses`. Check db jika masuk dan data approval ter-trigger.
