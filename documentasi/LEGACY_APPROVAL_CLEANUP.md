# Legacy Approval System Cleanup

## Overview
Dokumen ini mencatat pembersihan sistem approval lama (legacy) dan migrasi ke sistem approval dinamis baru.

## Tanggal Cleanup
**Tanggal:** 5 Februari 2026

## Perubahan yang Dilakukan

### 1. Model Changes

#### ✅ Dibersihkan dari Transaction Model
File: `app/Models/Transaction.php`

**Dihapus:**
- ❌ Relationship `threshold()` → ke `TransactionApprovalThreshold`
- ❌ Relationship `approvals()` → ke `TransactionApproval`
- ❌ Relationship `pendingApprovals()` → ke `TransactionApproval`
- ❌ Relationship `nextApprover()` → ke `TransactionApproval`
- ❌ Relationship `logs()` → ke `TransactionApprovalLog`
- ❌ Method `getNextPendingApproval()`
- ❌ Method `getCurrentApproverInfo()`

**Tetap Digunakan:**
- ✅ Relationship `approvalRequest()` → ke `ApprovalRequest` (dynamic system)
- ✅ Relationship `approvalRequestDetails()` → ke `ApprovalRequestDetail` (dynamic system)
- ✅ Status constants untuk `status_approval` field (dynamic system)
- ✅ Helper methods: `getApprovalStatusLabel()`, `getApprovalStatusBadgeClass()`, `isApprovalPending()`, dll.

### 2. Controller Changes

#### ✅ SubmissionController
File: `app/Http/Controllers/SubmissionController.php`

**Dihapus:**
- ❌ Import `App\Models\TransactionApproval`
- ❌ Import `App\Services\ApprovalService`
- ❌ Property `$approvalService`
- ❌ Constructor parameter `ApprovalService $approvalService`
- ❌ Fallback code ke legacy system di method `approve()`
- ❌ Fallback code ke legacy system di method `reject()`
- ❌ Fallback code ke legacy system di method `getBadgeInfo()`
- ❌ Legacy check di method `detail()`

**Tetap Digunakan:**
- ✅ `ApprovalTransactionService` untuk semua operasi approval
- ✅ Integrasi penuh dengan dynamic approval system

### 3. Legacy Models - Marked as Deprecated

Semua model legacy ditandai `@deprecated` dengan notice untuk menggunakan dynamic system:

| Model                          | Status       | Replacement                                    |
| ------------------------------ | ------------ | ---------------------------------------------- |
| `TransactionApproval`          | ⚠️ Deprecated | `ApprovalRequestDetail`                        |
| `TransactionApprovalLog`       | ⚠️ Deprecated | `ApprovalRequestDetail` (built-in timestamps)  |
| `TransactionApprovalThreshold` | ⚠️ Deprecated | `ApprovalFlowTemplate` + `ApprovalFlowDetail`  |
| `TransactionAuthorizer`        | ⚠️ Deprecated | `ApprovalFlowDetail`                           |
| `TransactionHistoryApproval`   | ⚠️ Deprecated | `ApprovalRequestDetail` (dengan status history) |

### 4. Legacy Service - Marked as Deprecated

| Service           | Status       | Replacement                  |
| ----------------- | ------------ | ---------------------------- |
| `ApprovalService` | ⚠️ Deprecated | `ApprovalTransactionService` |

## Dynamic Approval System (Current)

### Database Tables
```
approval_modules              → Modul yang menggunakan approval
approval_flow_templates       → Template/aturan approval per modul
approval_flow_details         → Detail approver untuk setiap template
approval_requests             → Header request approval per transaksi
approval_request_details      → Detail approver per request (snapshot)
```

### Service Layer
```
App\Services\ApprovalTransactionService\
├── ApprovalTransactionService.php       # Interface
└── ApprovalTransactionServiceImpl.php   # Implementation
```

### Key Features
- ✅ **Two-Phase Approval:** Uppline Chain → Master Flow
- ✅ **Threshold-Based:** Filter approver berdasarkan nominal
- ✅ **All-Levels Mode:** Semua level wajib approve
- ✅ **Immutable Snapshot:** Perubahan template tidak mempengaruhi request berjalan
- ✅ **Sequential Approval:** Level N approve sebelum Level N+1
- ✅ **Flexible Configuration:** Per module, per template

## Migration Path

### Untuk Data Lama
Data approval lama di tabel legacy **TIDAK** dihapus untuk historical record. Namun:
1. Semua transaksi baru menggunakan dynamic approval system
2. Legacy code sudah dihapus dari production code
3. Legacy models tetap ada tapi sudah deprecated

### Untuk Developer

**JANGAN gunakan lagi:**
```php
// ❌ DEPRECATED - Jangan gunakan
$transaction->threshold;
$transaction->approvals;
$transaction->pendingApprovals();
$this->approvalService->createApprovalChain();
TransactionApproval::where(...);
```

**GUNAKAN ini:**
```php
// ✅ CORRECT - Gunakan dynamic approval
$transaction->approvalRequest;
$transaction->approvalRequestDetails;
$this->approvalTransactionService->submitForApproval($transactionId);
ApprovalRequest::where(...);
ApprovalRequestDetail::where(...);
```

## Files Modified

### Models
- [x] `app/Models/Transaction.php` - Cleaned
- [x] `app/Models/TransactionApproval.php` - Deprecated
- [x] `app/Models/TransactionApprovalLog.php` - Deprecated
- [x] `app/Models/TransactionApprovalThreshold.php` - Deprecated
- [x] `app/Models/TransactionAuthorizer.php` - Deprecated
- [x] `app/Models/TransactionHistoryApproval.php` - Deprecated

### Controllers
- [x] `app/Http/Controllers/SubmissionController.php` - Cleaned

### Services
- [x] `app/Services/ApprovalService.php` - Deprecated

## Next Steps

### Phase 1 (Completed) ✅
- [x] Remove legacy code from Transaction model
- [x] Remove legacy code from SubmissionController
- [x] Mark legacy models as deprecated
- [x] Mark legacy service as deprecated
- [x] Create migration documentation

### Phase 2 (Future)
- [ ] Monitor untuk memastikan tidak ada error di production
- [ ] Migrasi data legacy ke dynamic system (optional)
- [ ] Hapus migration files untuk legacy tables (jika tidak dibutuhkan)

### Phase 3 (Future - After Stabilization)
- [ ] Remove legacy models completely
- [ ] Remove legacy service completely
- [ ] Remove legacy database tables (after backup)

## Testing Checklist

Setelah cleanup, pastikan testing untuk:

- [ ] ✅ Submit transaction baru → menggunakan dynamic approval
- [ ] ✅ Approve transaction → menggunakan dynamic approval
- [ ] ✅ Reject transaction → menggunakan dynamic approval
- [ ] ✅ View approval timeline → menggunakan dynamic approval
- [ ] ✅ View transaction detail → tidak error
- [ ] ✅ Pending approvals list → menggunakan dynamic approval
- [ ] ✅ Approval counts → menggunakan dynamic approval

## References

- [APPROVAL_SYSTEM.md](./APPROVAL_SYSTEM.md) - Dokumentasi lengkap dynamic approval system
- [ANTIGRAVITY_RULES.md](../ANTIGRAVITY_RULES.md) - Coding standards

## Contact

Jika ada pertanyaan atau issue terkait cleanup ini, silakan hubungi development team.

---
**Last Updated:** 5 Februari 2026
**Status:** ✅ Completed
