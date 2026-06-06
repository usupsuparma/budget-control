# Fitur Edit Submission - User Budget Control

## Deskripsi
Fitur edit submission telah ditambahkan pada halaman pengajuan anggaran user. User dapat mengedit submission yang masih berstatus **"Submission" (status = 0)** sebelum disetujui oleh approver.

## Fitur yang Ditambahkan

### 1. Tombol Edit pada Tabel
- Tombol edit hanya muncul untuk submission dengan status = 0 (Submission)
- Tombol edit terletak di kolom Action bersama dengan tombol View dan Delete
- Icon: `ri-edit-line` (icon pensil)
- Warna: Warning (kuning)

### 2. Fungsi Edit Submission
Fungsi `editSubmission(id)` telah diimplementasi dengan fitur lengkap:

#### Alur Kerja:
1. **Reset Form** - Membersihkan form sebelum mengisi data
2. **Fetch Data** - Mengambil data submission dari server
3. **Populate Form** - Mengisi form dengan data yang ada:
   - Transaction Date
   - Purpose
   - Urgency
   - Job Level
   - Job Position (dengan cascading dropdown)
   - Program ID (dengan cascading dropdown)
   - Budget Items (dengan cascading dropdown)
4. **Populate Items Table** - Mengisi tabel items dengan detail barang/jasa:
   - Goods/Service Name
   - Budget ID (dengan nilai budget yang tersedia)
   - Unit
   - Quantity
   - Price (dengan format ribuan)
   - Total (auto calculated)
5. **Calculate Total** - Menghitung total estimated value
6. **Show Modal** - Menampilkan modal edit

### 3. Cascading Dropdown pada Edit
Sistem cascading dropdown berfungsi otomatis saat edit:
- **Job Level → Job Position**: Memuat job positions berdasarkan job level yang dipilih
- **Job Level → Program**: Memuat programs berdasarkan job level
- **Program → Budget Items**: Memuat budget items berdasarkan program yang dipilih

### 4. Item Rows Management
- Setiap item row dapat diedit:
  - Nama barang/jasa
  - Budget yang dipilih (menampilkan budget value otomatis)
  - Unit
  - Quantity
  - Price (dengan format ribuan separator)
  - Total (auto-calculated)
- User dapat menghapus item row yang tidak diperlukan
- User dapat menambah item row baru

### 5. Form Validation
- Semua field required tetap divalidasi
- Error handling untuk:
  - Data tidak ditemukan
  - Permission denied
  - Network errors

## Routes yang Digunakan

```php
// Show submission data for edit
Route::get('/show/{id}', [SubmissionController::class, 'show'])
    ->name('userSubmission.show');

// Update submission
Route::put('/update/{id}', [SubmissionController::class, 'update'])
    ->name('userSubmission.update');

// Cascading dropdown routes
Route::get('/job-positions/{jobLevelId}', [SubmissionController::class, 'getJobPositions'])
    ->name('userSubmission.jobPositions');
    
Route::get('/programs/{jobLevelId}', [SubmissionController::class, 'getPrograms'])
    ->name('userSubmission.programs');
    
Route::get('/budget-items/{programId}', [SubmissionController::class, 'getBudgetItems'])
    ->name('userSubmission.budgetItems');
```

## Controller Methods yang Digunakan

### 1. show($id)
```php
public function show($id)
{
    // Mengambil data transaction dengan relasi
    // Memvalidasi ownership (user hanya bisa edit submission miliknya)
    // Return JSON response dengan data lengkap
}
```

### 2. update(Request $request, $id)
```php
public function update(Request $request, $id)
{
    // Validasi input
    // Check ownership
    // Check status (hanya status 0 yang bisa diedit)
    // Update transaction dan details
    // Return JSON response
}
```

## Cara Menggunakan

### Untuk User:
1. Buka halaman **Pengajuan Anggaran** (`/admission/user-submission`)
2. Lihat daftar submission yang ada di tabel
3. Untuk submission dengan status "Submission", akan muncul tombol kuning (Edit)
4. Klik tombol Edit
5. Modal edit akan terbuka dengan data yang sudah terisi
6. Ubah data yang diperlukan:
   - Tanggal transaksi
   - Job Level (akan reload job position dan program)
   - Job Position
   - Program ID (akan reload budget items)
   - Purpose
   - Items (tambah, edit, atau hapus item)
   - Urgency
7. Klik **Save** untuk menyimpan perubahan
8. Sistem akan memvalidasi dan menyimpan data
9. Tabel akan di-refresh otomatis setelah berhasil

### Batasan Edit:
- **Hanya submission dengan status 0 (Submission)** yang dapat diedit
- User hanya dapat edit submission miliknya sendiri
- Setelah submission diapprove atau direject, tidak dapat diedit lagi

## Teknologi yang Digunakan

### Frontend:
- **jQuery** - AJAX calls dan DOM manipulation
- **Bootstrap 5** - Modal dan styling
- **SweetAlert2** - Notification dan confirmation
- **Choices.js** - Enhanced select dropdown (opsional)

### Backend:
- **Laravel 10** - Framework
- **Eloquent ORM** - Database operations
- **Transaction & DB::beginTransaction()** - Untuk data consistency

## Error Handling

### 1. Permission Denied
```javascript
if (transaction->user_id != Auth::id()) {
    return response()->json([
        'success' => false,
        'message' => 'You do not have permission to edit this submission'
    ], 403);
}
```

### 2. Invalid Status
```javascript
if (transaction->status != 0) {
    return response()->json([
        'success' => false,
        'message' => 'This submission cannot be edited'
    ], 400);
}
```

### 3. Validation Errors
- Menampilkan error untuk setiap field yang tidak valid
- Highlight field dengan class `is-invalid`
- Menampilkan pesan error di bawah field

### 4. Network Errors
- Menampilkan SweetAlert dengan pesan error
- Log error ke console untuk debugging

## Testing Checklist

- [ ] Tombol edit muncul hanya untuk status = 0
- [ ] Modal edit terbuka dengan data yang benar
- [ ] Cascading dropdown berfungsi (Job Level → Job Position)
- [ ] Cascading dropdown berfungsi (Job Level → Program)
- [ ] Cascading dropdown berfungsi (Program → Budget Items)
- [ ] Item rows terisi dengan data yang benar
- [ ] Budget value muncul otomatis saat budget dipilih
- [ ] Quantity dan Price bisa diubah
- [ ] Total auto-calculated dengan benar
- [ ] Format ribuan pada price berfungsi
- [ ] Estimated value total dihitung dengan benar
- [ ] Bisa menghapus item row
- [ ] Bisa menambah item row baru
- [ ] Form validation berfungsi
- [ ] Data tersimpan ke database dengan benar
- [ ] Tabel ter-refresh setelah save
- [ ] Summary cards ter-refresh setelah save
- [ ] Error handling berfungsi dengan baik
- [ ] Permission check berfungsi (user lain tidak bisa edit)
- [ ] Status check berfungsi (status != 0 tidak bisa edit)

## Update Log

**Date:** 31 Desember 2025
**Updated by:** System
**Changes:**
- ✅ Implementasi lengkap fungsi `editSubmission(id)`
- ✅ Cascading dropdown untuk Job Level → Job Position
- ✅ Cascading dropdown untuk Job Level → Program
- ✅ Cascading dropdown untuk Program → Budget Items
- ✅ Dynamic item rows dengan data existing
- ✅ Auto-calculation untuk item totals dan estimated value
- ✅ Format ribuan untuk price input
- ✅ Error handling dan validation
- ✅ Permission dan status checking

## Notes
- Modal yang digunakan sama dengan modal Add Submission (`#submissionModal`)
- Title modal berubah dari "Add Submission" menjadi "Edit Submission"
- Hidden field `submissionId` diisi dengan ID submission yang akan diedit
- Fungsi `saveSubmission()` otomatis mendeteksi mode edit berdasarkan `submissionId`
