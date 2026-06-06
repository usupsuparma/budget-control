# Work Plan Budget Items - Modal Implementation

## Perubahan yang Dilakukan

### 1. Database Migration (2025_11_29_084450_create_workplan_budget_items_table.php)
- **Sebelum**: `activity_*` fields menggunakan `unsignedTinyInteger` (0-255)
- **Sesudah**: `activity_*` fields menggunakan `unsignedSmallInteger` (0-65535)
- **Alasan**: Untuk menampung nilai quantity dari 0 sampai 1000

**Note**: Migration file sudah diupdate, tapi TIDAK perlu di-migrate ulang. Jalankan SQL script manual:
```sql
-- Lihat file: database/migrations/UPDATE_ACTIVITY_FIELDS.sql
```

### 2. Model (WorkplanBudgetItem.php)
**Perubahan pada method `getActivityMonths()`:**
- Sebelum: Mengembalikan `active` (boolean) dengan check `=== 1`
- Sesudah: Mengembalikan `active` (boolean) dengan check `> 0` dan menambahkan `quantity`

**Penambahan method baru:**
```php
public function getTotalActivityQuantity(): int
```
Method untuk menghitung total quantity dari semua bulan.

### 3. Controller (WorkPlanItemController.php)
**Update validation rules:**
- Sebelum: `'activity_jan' => 'nullable|integer|in:0,1'`
- Sesudah: `'activity_jan' => 'nullable|integer|min:0|max:1000'`

Berlaku untuk semua 12 bulan (jan - dec).

### 4. View (work-plan-item.blade.php)
**Penambahan Modal:**
- Modal besar (modal-xl) untuk Add/Edit item
- Form lengkap dengan semua fields
- Input number untuk activity quantity (0-1000) untuk 12 bulan
- Auto-calculate total dari beg_balance * cons_rate

**Struktur Modal:**
- Header: Judul dinamis (Add/Edit)
- Body: 2 kolom form + section activity quantities
- Footer: Cancel & Save buttons

### 5. JavaScript (work-plan-item.js)
**Fungsi yang diubah/ditambah:**

#### Event Listeners
```javascript
- btn-add-item: Membuka modal Add
- btn-edit-item: Membuka modal Edit dengan data item
- btn-delete-item: Delete item dengan konfirmasi
- itemForm submit: Save item dari modal
- begBalance, consRate input: Auto-calculate total
```

#### Fungsi Modal
```javascript
openAddModal(categoryId)          // Buka modal untuk add item baru
openEditModal(itemId)              // Buka modal untuk edit item existing
saveItemFromModal()                // Save data dari modal (POST/PUT)
populateBudgetCodes(selectedCode)  // Populate dropdown budget codes
calculateModalTotal()              // Calculate total dari beg_balance * cons_rate
deleteItemById(itemId)             // Delete item by ID
```

#### Render Functions
```javascript
renderItemRow(item)                // Render row dengan display values (bukan input)
                                   // Menampilkan quantity sebagai angka, bukan checkbox
```

## Cara Penggunaan

### 1. Update Database Manual
```bash
# Jalankan SQL di database Anda:
mysql -u username -p database_name < database/migrations/UPDATE_ACTIVITY_FIELDS.sql
```

atau langsung di phpMyAdmin/MySQL client:
```sql
ALTER TABLE `workplan_budget_items` 
MODIFY COLUMN `activity_jan` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
MODIFY COLUMN `activity_feb` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
... (dst untuk semua bulan)
```

### 2. Clear Cache Laravel
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### 3. Test Fitur
1. Buka halaman Work Plan Items
2. Klik "Add Item" - modal akan muncul
3. Isi form dan activity quantities (0-1000)
4. Save
5. Test Edit item - klik icon pencil
6. Test Delete item - klik icon trash

## Fitur Modal

### Add Item
- Klik button "Add Item" di setiap kategori
- Modal terbuka dengan form kosong
- Semua activity quantities default = 0
- Budget code dropdown terisi otomatis

### Edit Item
- Klik icon pencil di row item
- Modal terbuka dengan data item pre-filled
- Bisa ubah semua field kecuali yang approved
- Activity quantities tampil sesuai data

### Activity Quantities
- Input type: number
- Range: 0 - 1000
- Default: 0
- 12 bulan: Jan - Dec

### Auto Calculate Total
- Total = Beginning Balance × Consumption Rate
- Calculate otomatis saat input beg_balance atau cons_rate
- Field total read-only

## Table Display
- Row menampilkan data (bukan input)
- Activity quantities ditampilkan sebagai angka
- Quantity > 0: Bold & biru
- Quantity = 0: Abu-abu muted
- Format number dengan locale Indonesia

## Status & Permission
- Draft/Rejected: Bisa edit & delete
- Approved: Hanya view (badge hijau "Approved")
- Pending: Tidak bisa edit (tergantung business logic)

## Validasi
- Description: Required
- Activity quantities: Integer, min 0, max 1000
- Total: Numeric, auto-calculated
- Budget code: Optional, dari master data

## Files yang Diubah
1. ✅ `database/migrations/2025_11_29_084450_create_workplan_budget_items_table.php`
2. ✅ `app/Models/WorkplanBudgetItem.php`
3. ✅ `app/Http/Controllers/WorkPlanItemController.php`
4. ✅ `resources/views/pages/work-plan/work-plan-item.blade.php`
5. ✅ `public/assets/js/work-plan-item.js`

## Files Baru
1. ✅ `database/migrations/UPDATE_ACTIVITY_FIELDS.sql` - SQL script untuk update manual

## Testing Checklist
- [ ] Update database menggunakan SQL script
- [ ] Clear Laravel cache
- [ ] Test Add item via modal
- [ ] Test Edit item via modal
- [ ] Test Delete item
- [ ] Test activity quantities (input 0-1000)
- [ ] Test auto-calculate total
- [ ] Verify data tersimpan dengan benar di database
- [ ] Test approved item (tidak bisa edit/delete)

## Notes
- Modal menggunakan Bootstrap 5
- SweetAlert2 untuk notifikasi
- jQuery untuk AJAX
- Tooltips Bootstrap untuk button actions
- Responsive modal (modal-xl)
