# MacframeGA Import Feature

## Overview
Fitur ini memungkinkan pengguna untuk mengimpor data transaksi pengajuan anggaran dari file Excel ekspor MacframeGA ke dalam modul Submission (Pengajuan Anggaran). Proses ini menggunakan mekanisme **Two-Phase Import** untuk memastikan integritas data dan pemilihan target anggaran yang tepat.

## Workflow: Two-Phase Import

### Phase 1: Upload & Preview (`previewMacframeImport`)
1. Pengguna mengunggah file Excel MacframeGA.
2. Sistem melakukan parsing terhadap struktur file MacframeGA:
   - **Row 2**: Mengambil tanggal transaksi.
   - **Row 4+**: Mengambil detail item (Nama Barang, Deskripsi/Purpose, Urgency, Unit, Qty, Harga).
3. Sistem memetakan Unit ke master data `units`. Jika tidak ditemukan, unit ditandai sebagai unresolved.
4. Hasil parsing dikembalikan ke frontend dalam bentuk JSON untuk ditampilkan di modal preview.

### Phase 2: Configuration & Commit (`commitMacframeImport`)
1. Pengguna melihat data preview di modal.
2. **Mandatory Configuration**:
   - Pengguna harus memilih **Tanggal Transaksi** (default dari file).
   - Pengguna harus memilih **Program ID (KPI Workplan)** sebagai target pengajuan.
   - Pengguna harus memasukkan **Purpose** dan **Urgency** global.
   - **Pemilihan Budget ID**: Pengguna WAJIB memilih budget item dari program yang dipilih untuk setiap baris data.
3. Setelah dikonfirmasi, data dikirim ke backend.
4. Sistem menyimpan header transaksi (`transactions`) dan detail transaksi (`transaction_details`).
5. Transaksi otomatis diajukan ke alur approval (`submitForApproval`).

## Technical Implementation

### Controller & Service
- **Controller**: `App\Http\Controllers\SubmissionController`
  - `previewMacframeImport`: Handling upload awal.
  - `commitMacframeImport`: Handling penyimpanan final dengan validasi budget.
- **Service Interface**: `App\Services\SubmissionService\SubmissionService`
- **Implementation**: `App\Services\SubmissionService\SubmissionServiceImpl`
  - `parseMacframeFile`: Logika parsing Excel menggunakan `Maatwebsite\Excel`.
  - `commitMacframeTransactions`: Logika penyimpanan database di dalam `DB::transaction`.

### Data Mapping
| Macframe Column | Target Field | Notes |
|---|-|---|
| Column 0 (Row 2) | `transaction_date` | Tanggal master file |
| Column 1 | `goods_service_name` | Nama barang/jasa |
| Column 2 | `purpose` | Deskripsi detail |
| Column 3 | `urgency` | Deskripsi konten |
| Column 5 | `unit_id` / `unit_name` | Mapping ke tabel `units` |
| Column 6 | `quantity` | Jumlah |
| Column 7 | `price` | Harga satuan |

## Critical Rules
1. **Budget ID Obligation**: Setiap baris item harus memiliki `budget_id` yang valid dari Program yang dipilih.
2. **Transaction Integrity**: Seluruh proses simpan dilakukan di dalam closure `DB::transaction`.
3. **Auto-Approval Submission**: Setelah disimpan, transaksi langsung masuk ke sistem approval dinamis (Phase 1: Uppline Chain).
