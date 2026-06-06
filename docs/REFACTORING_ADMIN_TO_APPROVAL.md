# Refactoring: Admin to Approval Submission

## Tanggal Perubahan
2 Februari 2026

## Deskripsi
Melakukan refactoring untuk mengganti prefix "admin" menjadi "approval" pada module submission karena tidak sesuai dengan peruntukan. Module ini sebenarnya adalah untuk approval submission, bukan admin submission.

## Perubahan yang Dilakukan

### 1. Routes (`routes/web.php`)
- ✅ Prefix route: `admission/admin` → `admission/approval`
- ✅ Middleware permission: `transaction.admin.view` → `transaction.approval.view`
- ✅ Route names: `adminSubmission.*` → `approvalSubmission.*`
  - `adminSubmission.index` → `approvalSubmission.index`
  - `adminSubmission.pendingApprovals` → `approvalSubmission.pendingApprovals`
  - `adminSubmission.show` → `approvalSubmission.show`
  - `adminSubmission.approve` → `approvalSubmission.approve`
  - `adminSubmission.reject` → `approvalSubmission.reject`

### 2. Controller (`app/Http/Controllers/SubmissionController.php`)
- ✅ Method name: `admin()` → `approval()`
- ✅ View title: "Submission Admin" → "Approval Submission"
- ✅ View path: `pages.submission.admin` → `pages.submission.approval`

### 3. View Files
- ✅ Rename file: `admin.blade.php` → `approval.blade.php`
- ✅ Update semua route references di view:
  - `route('adminSubmission.pendingApprovals')` → `route('approvalSubmission.pendingApprovals')`
  - `route('adminSubmission.show')` → `route('approvalSubmission.show')`
  - `route('adminSubmission.approve')` → `route('approvalSubmission.approve')`
  - `route('adminSubmission.reject')` → `route('approvalSubmission.reject')`

### 4. Sidebar (`resources/views/include/sidebar.blade.php`)
- ✅ Update permission checks: `transaction.admin.view` → `transaction.approval.view`
- ✅ Update route references: `adminSubmission.index` → `approvalSubmission.index`
- ✅ Update active path check: `admission/admin*` → `admission/approval*`
- ✅ Update di semua menu items yang menggunakan route ini

## URL yang Berubah

### Sebelum
```
/admission/admin
/admission/admin/pending-approvals
/admission/admin/{id}
/admission/admin/{id}/approve
/admission/admin/{id}/reject
```

### Sesudah
```
/admission/approval
/admission/approval/pending-approvals
/admission/approval/{id}
/admission/approval/{id}/approve
/admission/approval/{id}/reject
```

## Permission yang Berubah

### Sebelum
```
transaction.admin.view
```

### Sesudah
```
transaction.approval.view
```

## Dampak Perubahan

### ✅ Yang Harus Dilakukan Setelah Deployment
1. **Update Permission di Database**
   ```sql
   -- Update permission name
   UPDATE permissions 
   SET name = 'transaction.approval.view' 
   WHERE name = 'transaction.admin.view';
   
   -- Update guard_name if needed
   UPDATE permissions 
   SET guard_name = 'web' 
   WHERE name = 'transaction.approval.view';
   ```

2. **Clear Cache**
   ```bash
   php artisan route:clear
   php artisan view:clear
   php artisan config:clear
   php artisan cache:clear
   php artisan permission:cache-reset
   ```

3. **Update Role Permissions**
   - Pastikan semua role yang sebelumnya memiliki `transaction.admin.view` sekarang memiliki `transaction.approval.view`
   - Atau jalankan seeder untuk re-assign permissions

### ⚠️ Breaking Changes
- URL lama (`/admission/admin/*`) tidak akan berfungsi lagi
- Permission lama (`transaction.admin.view`) harus diupdate
- User yang memiliki bookmark ke URL lama perlu update

### ✅ Backward Compatibility
Tidak ada backward compatibility karena ini adalah breaking change yang disengaja untuk perbaikan naming convention.

## Testing Checklist

- [ ] Test akses menu "Approval Submission" di sidebar
- [ ] Test load page `/admission/approval`
- [ ] Test load pending approvals
- [ ] Test view transaction detail
- [ ] Test approve transaction
- [ ] Test reject transaction
- [ ] Test permission check untuk user yang memiliki `transaction.approval.view`
- [ ] Test permission check untuk user yang TIDAK memiliki `transaction.approval.view`

## Files Modified

1. `routes/web.php`
2. `app/Http/Controllers/SubmissionController.php`
3. `resources/views/pages/submission/approval.blade.php` (renamed from admin.blade.php)
4. `resources/views/include/sidebar.blade.php`

## Compliance

✅ Sesuai dengan `ANTIGRAVITY_RULES.md`:
- Route naming: `{module}.{action}` → `approvalSubmission.*`
- Permission naming: `{module}.{action}` → `transaction.approval.view`
- Controller orchestrator pattern maintained
- View naming convention followed

---

**Status:** ✅ COMPLETED  
**Reviewed by:** -  
**Approved by:** -
