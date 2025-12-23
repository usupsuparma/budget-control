# Dokumentasi Sistem Approval Dinamis Laravel

## 📋 Daftar Isi
1. [Setup & Installation](#setup--installation)
2. [Struktur Database](#struktur-database)
3. [Cara Kerja Sistem](#cara-kerja-sistem)
4. [Testing](#testing)
5. [API Documentation](#api-documentation)

---

## 🚀 Setup & Installation

### 1. Jalankan Migration

```bash
# Jalankan semua migration
php artisan migrate

# Atau jalankan migration secara terpisah
php artisan migrate --path=/database/migrations/2024_01_01_000001_create_approval_thresholds_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000002_modify_transaction_authorizer_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000003_modify_transactions_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000004_modify_transaction_approvals_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000005_create_approval_logs_table.php
php artisan migrate --path=/database/migrations/2024_01_01_000006_create_approval_delegations_table.php
```

### 2. Jalankan Seeder

```bash
# Jalankan semua seeder
php artisan db:seed

# Atau jalankan seeder spesifik
php artisan db:seed --class=ApprovalThresholdSeeder
php artisan db:seed --class=TransactionAuthorizerSeeder
php artisan db:seed --class=SampleTransactionSeeder
```

### 3. Rollback (jika diperlukan)

```bash
# Rollback semua migration
php artisan migrate:rollback

# Rollback dan migrate ulang
php artisan migrate:fresh --seed
```

---

## 📊 Struktur Database

### Tabel Utama

#### 1. **approval_thresholds**
Menyimpan konfigurasi threshold approval berdasarkan nominal

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| min_amount | decimal(15,2) | Nominal minimum |
| max_amount | decimal(15,2) | Nominal maksimum |
| approval_sequence | tinyint | Jumlah level approval |
| required_levels | JSON | Array level yang diperlukan |
| description | varchar(255) | Deskripsi threshold |
| is_active | boolean | Status aktif |

**Contoh Data:**
```json
{
  "min_amount": 0,
  "max_amount": 10000000,
  "approval_sequence": 2,
  "required_levels": [1, 2],
  "description": "Approval s/d Manager Finance"
}
```

#### 2. **transaction_authorizer** (Modified)
Menyimpan data approver dengan level dan authority

| Column Baru | Type | Description |
|-------------|------|-------------|
| position_code | varchar(50) | Kode jabatan |
| approval_level | tinyint | Level approval (1-5) |
| max_approval_amount | decimal(15,2) | Max nominal yang bisa approve |
| can_override | boolean | Bisa override level dibawah |
| priority_order | integer | Urutan prioritas |

#### 3. **transactions** (Modified)
Tracking status approval transaction

| Column Baru | Type | Description |
|-------------|------|-------------|
| threshold_id | bigint | FK ke approval_thresholds |
| current_approval_level | tinyint | Level approval saat ini |
| required_approval_levels | tinyint | Total level yang dibutuhkan |
| approval_completed_at | timestamp | Waktu approval selesai |
| rejection_reason | text | Alasan reject |

#### 4. **transaction_approvals** (Modified)
Detail setiap approval dengan urutan

| Column Baru | Type | Description |
|-------------|------|-------------|
| threshold_id | bigint | FK ke approval_thresholds |
| is_required | boolean | Apakah wajib |
| sequence_order | tinyint | Urutan approval |
| notified_at | timestamp | Waktu notifikasi dikirim |
| reminder_count | tinyint | Jumlah reminder |
| approval_method | varchar(50) | Method approval |
| ip_address | varchar(45) | IP address saat approve |

#### 5. **approval_logs** (Baru)
Audit trail semua aktivitas approval

| Column | Type | Description |
|--------|------|-------------|
| transaction_id | bigint | FK ke transactions |
| approval_id | bigint | FK ke transaction_approvals |
| action | varchar(50) | create, approve, reject, dll |
| actor_id | bigint | User yang melakukan action |
| notes | text | Catatan/komentar |
| metadata | JSON | Data tambahan |

---

## 🔄 Cara Kerja Sistem

### Flow Approval Dinamis

```
1. User Create Transaction
   ↓
2. System Check Threshold berdasarkan estimated_amount
   ↓
3. System Generate Approval Chain sesuai required_levels
   ↓
4. Notifikasi ke Approver Level 1
   ↓
5. Approver Level 1 Approve/Reject
   ↓
6. Jika Approve → Lanjut ke Level berikutnya
   Jika Reject → Transaction ditolak
   ↓
7. Ulangi sampai semua level approve
   ↓
8. Transaction Fully Approved
```

### Contoh Scenario

#### Scenario 1: Pengajuan 8 Juta
```
Amount: Rp 8.000.000
Threshold: 0 - 10 juta
Required Levels: [1, 2] (Supervisor & Manager)

Flow:
1. Supervisor Finance approve ✓
2. Manager Finance approve ✓
3. APPROVED ✓
```

#### Scenario 2: Pengajuan 50 Juta
```
Amount: Rp 50.000.000
Threshold: 10 juta - 100 juta
Required Levels: [1, 2, 3] (Supervisor, Manager & Direktur)

Flow:
1. Supervisor Finance approve ✓
2. Manager Finance approve ✓
3. Direktur Finance approve ✓
4. APPROVED ✓
```

#### Scenario 3: Pengajuan 500 Juta
```
Amount: Rp 500.000.000
Threshold: 100 juta - 1 miliar
Required Levels: [1, 2, 3, 4] (Sampai CEO)

Flow:
1. Supervisor Finance approve ✓
2. Manager Finance approve ✓
3. Direktur Finance reject ✗
4. REJECTED ✗
```

---

## 🧪 Testing

### Test Manual dengan Tinker

```bash
php artisan tinker
```

#### 1. Test Create Transaction

```php
use App\Models\Transaction;
use App\Services\ApprovalService;

$service = new ApprovalService();

// Create transaction 8 juta (sampai Manager)
$transaction = Transaction::create([
    'transaction_date' => now(),
    'user_id' => 1,
    'user_name' => 'John Doe',
    'unit_id' => 1,
    'unit_name' => 'IT Department',
    'purpose' => 'Test pengajuan 8 juta',
    'estimated_amount' => 8000000,
    'actual_amount' => 0,
    'urgency' => 'medium',
    'status' => 0,
]);

// Generate approval chain
$result = $service->createApprovalChain($transaction->id);

// Check approval chain
$transaction->fresh()->approvals;
```

#### 2. Test Approval Process

```php
use App\Models\TransactionApproval;

// Get pending approval pertama
$approval = TransactionApproval::where('transaction_id', 1)
    ->where('status', 0)
    ->orderBy('sequence_order')
    ->first();

// Approve
$result = $service->processApproval(
    $approval->id,
    TransactionApproval::STATUS_APPROVED,
    1001, // approver_id
    'Budi Santoso', // approver_name
    'Setuju untuk diproses' // comments
);

// Check status transaction
$approval->transaction->fresh();
```

#### 3. Test Check Threshold

```php
// Check threshold untuk berbagai amount
$threshold1 = $service->determineApprovalFlow(5000000); // 5 juta
$threshold2 = $service->determineApprovalFlow(50000000); // 50 juta
$threshold3 = $service->determineApprovalFlow(500000000); // 500 juta
```

---

## 📡 API Documentation

### Authentication
Semua API endpoint memerlukan authentication menggunakan Laravel Sanctum.

```bash
# Login dan dapatkan token
POST /api/login
Body: { "email": "user@example.com", "password": "password" }

# Gunakan token di header
Authorization: Bearer {your-token}
```

### Endpoints

#### 1. Create Transaction

```bash
POST /api/transactions
Content-Type: application/json

{
  "transaction_date": "2024-12-17",
  "unit_id": 1,
  "unit_name": "IT Department",
  "purpose": "Pembelian laptop",
  "estimated_amount": 8000000,
  "urgency": "high"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Transaction created successfully",
  "data": {
    "transaction": { ... },
    "approvals": [ ... ]
  }
}
```

#### 2. Get Pending Approvals

```bash
GET /api/approvals/pending
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "transaction_id": 1,
      "approval_level": 1,
      "sequence_order": 1,
      "transaction": {
        "purpose": "Pembelian laptop",
        "estimated_amount": 8000000
      }
    }
  ]
}
```

#### 3. Approve Transaction

```bash
POST /api/approvals/{id}/approve
Content-Type: application/json

{
  "comments": "Approved, silakan diproses"
}
```

#### 4. Reject Transaction

```bash
POST /api/approvals/{id}/reject
Content-Type: application/json

{
  "comments": "Ditolak karena budget tidak mencukupi"
}
```

#### 5. Check Threshold for Amount

```bash
POST /api/thresholds/check
Content-Type: application/json

{
  "amount": 50000000
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "threshold": {
      "id": 2,
      "min_amount": 10000001,
      "max_amount": 100000000,
      "description": "Approval s/d Direktur Finance"
    },
    "approval_levels_required": 3,
    "required_levels": [1, 2, 3]
  }
}
```

---

## 🔧 Konfigurasi Tambahan

### 1. Notifications (Opsional)

Buat notification class untuk mengirim email/push notification:

```bash
php artisan make:notification ApprovalRequestNotification
php artisan make:notification ApprovalReminderNotification
```

### 2. Queue untuk Async Processing

Setup queue untuk mengirim notifikasi secara async:

```bash
# .env
QUEUE_CONNECTION=database

# Jalankan queue worker
php artisan queue:work
```

### 3. Scheduler untuk Auto Reminder

Tambahkan di `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Kirim reminder setiap hari jam 9 pagi
    $schedule->call(function () {
        // Logic untuk kirim reminder ke pending approvals
    })->dailyAt('09:00');
}
```

---

## 📝 Notes Penting

1. **Validasi Amount**: Pastikan tidak ada gap di threshold (contoh: 0-10jt, 10jt-100jt bukan 0-10jt, 11jt-100jt)

2. **Soft Delete**: Semua tabel menggunakan soft delete untuk menjaga data history

3. **Audit Trail**: Semua aktivitas tercatat di `approval_logs` untuk keperluan audit

4. **Level Approval**: 
   - Level 1: Supervisor
   - Level 2: Manager
   - Level 3: Direktur
   - Level 4: CEO
   - Level 5: Board of Directors

5. **Sequential Approval**: Approval harus berurutan, tidak bisa skip level

6. **Delegation**: Fitur delegation memungkinkan approver mendelegasikan approval ke orang lain

---

## 🐛 Troubleshooting

### Error: No threshold found
**Solusi**: Pastikan ada threshold yang cover semua range amount, atau tambah threshold default dengan max unlimited.

### Error: No authorizer found for level
**Solusi**: Pastikan setiap level di `required_levels` ada authorizer yang aktif di tabel `transaction_authorizer`.

### Approval stuck di level tertentu
**Solusi**: Check `current_approval_level` vs `required_approval_levels`, dan pastikan status approval di level tersebut sudah processed.

---

## 📞 Support

Jika ada pertanyaan atau issue, silakan:
1. Check dokumentasi ini terlebih dahulu
2. Review log di `storage/logs/laravel.log`
3. Gunakan `php artisan tinker` untuk debugging

---

## 🎯 Next Steps / Improvement Ideas

1. ✅ Add email notification
2. ✅ Add mobile push notification
3. ✅ Add dashboard analytics
4. ✅ Add bulk approval feature
5. ✅ Add auto-escalation after X days
6. ✅ Add approval by email link
7. ✅ Add multi-currency support
8. ✅ Add conditional approval based on category