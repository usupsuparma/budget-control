# Refactor Approval Page - Three Tabs with AJAX

## Overview
Halaman approval telah direfactor dari 2 tab (All Transactions & Approval Queue) menjadi 3 tab (Approval, Approved, Rejected) dengan semua proses menggunakan AJAX tanpa reload page.

## Changes Made

### 1. Frontend Changes (approval.blade.php)

#### Tab Structure
- **Before:**
  - Tab 1: All Transactions (table dengan semua transaksi)
  - Tab 2: Approval Queue (list pending approvals)

- **After:**
  - Tab 1: **Approval** (pending approvals - yang perlu di-approve)
  - Tab 2: **Approved** (yang sudah di-approve oleh user)
  - Tab 3: **Rejected** (yang sudah di-reject oleh user)

#### JavaScript Functions

**New Functions:**
1. `loadBadgeCounts()` - Load badge counts untuk ketiga tab
2. `loadApprovalTab(status, page)` - Main function untuk load data tab (pending/approved/rejected)
3. `renderApprovalList(data, containerId, status)` - Render list items dalam format card

**Removed Functions:**
- `loadData()` - Removed (diganti dengan `loadApprovalTab`)
- `renderTable()` - Removed (diganti dengan `renderApprovalList`)
- `renderPagination()` - Removed (tidak perlu pagination di card view)
- `loadPendingApprovalCount()` - Removed (diganti dengan `loadBadgeCounts`)
- `loadPendingApprovals()` - Removed (diganti dengan `loadApprovalTab`)
- `renderPendingApprovals()` - Removed (diganti dengan `renderApprovalList`)
- `renderEmptyApprovalState()` - Removed (handling empty state dipindah ke `renderApprovalList`)
- `changePage()` - Removed (tidak diperlukan)

**Updated Functions:**
- `$(document).ready()` - Update initialization untuk load tab approval
- `processApproval()` - Update untuk reload tab yang aktif setelah approve/reject

### 2. Backend Changes

#### Routes (web.php)
**New Routes:**
```php
// Get badge counts for all tabs
Route::get('/counts', [SubmissionController::class, 'getApprovalCounts'])
    ->name('userSubmission.approval.counts');

// Get approval data for specific tab
Route::get('/data', [SubmissionController::class, 'getApprovalData'])
    ->name('userSubmission.approval.data');
```

#### Controller (SubmissionController.php)
**New Methods:**

1. **getApprovalCounts(Request $request)**
   - Get count untuk pending, approved, dan rejected
   - Support filter by year
   - Return: `{ pending: int, approved: int, rejected: int }`

2. **getApprovalData(Request $request)**
   - Get paginated data untuk tab tertentu (pending/approved/rejected)
   - Support filters: status, year, search
   - Return: Paginated transaction data with approval details

**Parameters:**
- `status`: 'pending', 'approved', or 'rejected'
- `year`: Year filter (optional, 'all' for no filter)
- `search`: Search by purpose or transaction number (optional)
- `page`: Page number for pagination
- `per_page`: Items per page (default: 10)

## API Endpoints

### 1. GET /approval/counts
**Description:** Get badge counts for all three tabs

**Request:**
```javascript
GET /approval/counts?year=2026
```

**Response:**
```json
{
  "success": true,
  "data": {
    "pending": 5,
    "approved": 20,
    "rejected": 3
  }
}
```

### 2. GET /approval/data
**Description:** Get paginated approval data for specific tab

**Request:**
```javascript
GET /approval/data?status=pending&year=2026&search=budget&page=1
```

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "transaction_number": "TRX-2026-001",
        "transaction_date": "2026-01-15",
        "user_name": "John Doe",
        "purpose": "Budget request for...",
        "urgency": "high",
        "estimated_amount": 10000000,
        "status": 0,
        "can_approve": true,
        "can_approve_detail_id": 123,
        "approval_status": "pending",
        "approved_at": null
      }
    ],
    "current_page": 1,
    "last_page": 3,
    "per_page": 10,
    "total": 25,
    "from": 1,
    "to": 10,
    "prev_page_url": null,
    "next_page_url": "http://example.com/approval/data?page=2"
  }
}
```

## Features

### 1. Real-time Badge Updates
- Badge counts di-update setiap 30 detik
- Badge counts di-update setelah approve/reject action
- Badge pending (merah) hanya muncul jika ada pending approvals

### 2. Filter Support
- Filter by year (All Years, 2026, 2025, etc.)
- Filter berlaku untuk semua 3 tab

### 3. Card Layout
- Data ditampilkan dalam format card yang lebih user-friendly
- Setiap card menampilkan:
  - Transaction number
  - Date
  - Submitter name
  - Purpose (truncated)
  - Estimated amount
  - Urgency badge
  - Status badge
  - Action buttons (View, Approve, Reject)

### 4. Tab-Specific Actions
- **Approval Tab:** Show approve/reject buttons (jika can_approve = true)
- **Approved Tab:** Show view button only
- **Rejected Tab:** Show view button only

### 5. No Page Reload
- Semua proses menggunakan AJAX
- Tab switching tanpa reload
- Filter tanpa reload
- Approve/reject tanpa reload

## Usage Flow

### For Approvers:
1. Buka halaman `/approval`
2. Default tab: **Approval** (list pending approvals)
3. User dapat:
   - Klik "View" untuk melihat detail
   - Klik "Approve" untuk approve
   - Klik "Reject" untuk reject
4. Setelah action, tab akan auto-refresh via AJAX
5. Badge counts akan auto-update

### Tab Navigation:
- Klik tab **Approved** untuk melihat yang sudah di-approve
- Klik tab **Rejected** untuk melihat yang sudah di-reject
- Filter by year berlaku untuk semua tab

## Technical Notes

### Database Queries
- Menggunakan `ApprovalRequestDetail` sebagai base query
- Filter by `employee_id` untuk current user
- Join dengan `approval_requests` dan `transactions` untuk get detail
- Support eager loading untuk optimize performance

### Performance Optimization
- Pagination untuk avoid loading terlalu banyak data
- Eager loading relationships
- Badge counts di-cache di frontend (reload every 30s)

### Error Handling
- Try-catch di semua AJAX calls
- Show user-friendly error messages
- Log errors ke console untuk debugging
- Fallback UI jika data kosong atau error

## Testing Checklist

- [ ] Load approval tab (pending) berhasil
- [ ] Load approved tab berhasil
- [ ] Load rejected tab berhasil
- [ ] Badge counts ter-update dengan benar
- [ ] Filter by year berfungsi
- [ ] Approve transaction dari tab Approval
- [ ] Reject transaction dari tab Approval
- [ ] Tab auto-refresh setelah approve/reject
- [ ] Badge counts ter-update setelah approve/reject
- [ ] View detail berfungsi dari semua tab
- [ ] Empty state muncul jika tidak ada data
- [ ] Error handling berfungsi jika API gagal
- [ ] Badge counts reload setiap 30 detik

## Migration Notes

### Breaking Changes:
- Endpoint `/approval/data` (GET) sekarang memerlukan parameter `status`
- Old functions `loadData()`, `loadPendingApprovals()` sudah dihapus

### Backward Compatibility:
- Route `/pending-approvals` tetap ada untuk compatibility
- Existing approve/reject endpoints tidak berubah

## Future Enhancements

1. **Search Functionality**
   - Tambahkan search input per tab
   - Search by transaction number, purpose, or submitter name

2. **Advanced Filters**
   - Filter by urgency (high, medium, low)
   - Filter by amount range
   - Filter by department

3. **Bulk Actions**
   - Select multiple items untuk bulk approve/reject

4. **Notifications**
   - Browser notification untuk pending approvals
   - Email notification

5. **Export**
   - Export approved/rejected list ke Excel
   - Generate report per period

## Troubleshooting

### Badge counts tidak muncul
- Check route `userSubmission.approval.counts` sudah terdaftar
- Check method `getApprovalCounts` di controller
- Check console log untuk errors

### Data tidak muncul di tab
- Check route `userSubmission.approval.data` sudah terdaftar
- Check method `getApprovalData` di controller
- Check parameter `status` dikirim dengan benar
- Check console log untuk API response

### Approve/Reject tidak bekerja
- Check existing routes masih ada (`approvalSubmission.approve`, `approvalSubmission.reject`)
- Check `processApproval()` function memanggil `loadApprovalTab()` dengan benar
- Check console log untuk errors
