# Budget Verification Auto-Submit Logging

## Ringkasan

Saat verifikator menyelesaikan proses `Budget Items Pending Verification`, sistem langsung memanggil auto-submit ke flow approval `workplan_budget_items`. Sebelumnya, jika submit approval gagal, log yang tertinggal terlalu tipis sehingga penyebab error sulit ditelusuri.

Perubahan ini menambahkan `debug_ref` yang konsisten dari proses verifikasi ke proses approval agar error bisa dicocokkan dari UI ke log aplikasi.

## Alur yang Dicatat

1. Verifikator menjalankan endpoint:
   - `POST /budget-verification/{itemId}/verify`
2. `VerificationBudgetServiceImpl::verifyBudget()`:
   - menyimpan hasil verifikasi
   - mengubah item menjadi `verification_status = verified`
   - memanggil `WorkplanBudgetItemApprovalService::submitForApproval()`
3. Jika auto-submit approval gagal:
   - service approval menulis log warning/error dengan `debug_ref`
   - service verification menulis log error lanjutan dengan:
     - `verification_debug_ref`
     - `approval_debug_ref`
     - snapshot item
     - payload response approval
   - response JSON membawa reference yang sama agar bisa tampil di UI

## Perubahan File

- `app/Services/VerificationBudgetService/VerificationBudgetServiceImpl.php`
  - menambah `verification_debug_ref`
  - menambah log start/failure untuk auto-submit approval
  - meneruskan `approval_debug_ref` ke response JSON
- `app/Services/WorkplanBudgetItemApprovalService.php`
  - menambah `debug_ref` untuk semua jalur gagal `submitForApproval()`
  - menambah context item/auth user pada log failure
- `app/Http/Controllers/VerificationBudgetController.php`
  - menulis log tambahan saat verifikasi sukses tetapi approval tidak berhasil dikirim
- `resources/views/pages/budget/budget-user.blade.php`
- `public/assets/js/budget-user.js`
  - menampilkan `Ref: ...` pada notifikasi agar mudah dicari di log

## Format Response

Pada jalur partial success, response verifikasi sekarang dapat membawa:

```json
{
  "success": true,
  "message": "Verifikasi berhasil, tetapi auto-submit approval gagal: ...",
  "debug_ref": "APR-TRACE-001",
  "data": {
    "approval_submitted": false,
    "approval_debug_ref": "APR-TRACE-001",
    "verification_debug_ref": "VER-TRACE-001"
  }
}
```

Pada jalur gagal di `submitForApproval()`, response approval juga membawa:

```json
{
  "success": false,
  "message": "...",
  "debug_ref": "APR-TRACE-001",
  "data": {
    "debug_ref": "APR-TRACE-001"
  }
}
```

## Cara Pakai Saat Debug

1. Jalankan verifikasi dari tab `Pending Verification`.
2. Jika muncul pesan dengan suffix `Ref: ...`, salin reference tersebut.
3. Cari di log Laravel:

```bash
grep "APR-TRACE-001" storage/logs/laravel.log
```

4. Lihat pasangan log:
   - `WorkplanBudgetItemApprovalService.submitForApproval failed`
   - `VerificationBudgetService.verifyBudget auto-submit approval failed`

## Catatan

- Perubahan ini menambah observability, bukan mengubah rule approval.
- Reference log sengaja ikut dikembalikan ke UI agar troubleshooting bisa dilakukan tanpa menebak timestamp log.
