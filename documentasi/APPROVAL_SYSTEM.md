# 📋 DOKUMENTASI SISTEM APPROVAL DINAMIS

## 📑 Daftar Isi

1. [Overview Sistem](#1-overview-sistem)
2. [Konsep Dasar](#2-konsep-dasar)
3. [Arsitektur Database](#3-arsitektur-database)
4. [Flow Proses](#4-flow-proses)
5. [Panduan Konfigurasi](#5-panduan-konfigurasi)

---

## 1. OVERVIEW SISTEM

### 1.1 Tujuan Sistem

Sistem approval dinamis untuk mengelola proses persetujuan pada modul bisnis: **Transactions**, **Budget**, **LPJ Transactions**.

### 1.2 Fitur Utama

| Fitur                    | Keterangan                                            |
| ------------------------ | ----------------------------------------------------- |
| **Multi-Level Approval** | Approval bertingkat (1, 2, 3+ level)                  |
| **Uppline Chain**        | Pre-approval dari atasan langsung sebelum master flow |
| **Threshold-Based**      | Flow berdasarkan nominal (amount)                     |
| **All-Levels**           | Flow tanpa nominal (semua level wajib)                |
| **Sequential**           | Approval berurutan (Level 1 → Level 2 → dst)          |
| **Immutable Snapshot**   | Perubahan aturan tidak mempengaruhi proses berjalan   |

### 1.3 Contoh Flow

```
Employee A submit Invoice 90 juta:

PHASE 1: UPPLINE CHAIN
├─ Employee B (atasan langsung) → approve
└─ Employee C (atasan Employee B)   → approve

PHASE 2: MASTER FLOW (threshold-based)
├─ Employee D (threshold 10jt)  → approve (90jt > 10jt)
└─ Employee E (threshold 100jt) → approve (90jt ≤ 100jt) ✓ DONE

Total: 4 approvers
```

---

## 2. KONSEP DASAR

### 2.1 Terminologi

| Term              | Pengertian                                                   |
| ----------------- | ------------------------------------------------------------ |
| **Module**        | Modul yang pakai approval (transactions, bookings, invoices) |
| **Uppline**       | Atasan langsung (dari field `uppline_id` di users)           |
| **Uppline Chain** | Rantai atasan sampai `uppline_id = NULL`                     |
| **Threshold**     | Batas nominal yang menentukan level aktif                    |
| **Phase**         | Fase approval: `uppline` atau `master_flow`                  |

### 2.2 Prinsip Desain

**Two-Phase Approval:**

```
Request Submit → PHASE 1: Uppline Chain → PHASE 2: Master Flow → APPROVED
```

**Threshold Logic (amount-based):**

```
Pengajuan 90 juta:
├─ Level 1 (threshold 10jt)  → AKTIF (90jt > 10jt, perlu level lebih tinggi)
├─ Level 2 (threshold 100jt) → AKTIF (90jt ≤ 100jt, final level)
└─ Level 3 (threshold 1M)    → SKIP (tidak diperlukan)
```

**All-Levels (non-amount):**

```
Semua level wajib approve, tanpa threshold filtering.
```

---

## 3. ARSITEKTUR DATABASE

### 3.1 Kapan Setiap Tabel Digunakan

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    ALUR PENGGUNAAN TABEL                                     │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│   🚀 SETUP AWAL (1x oleh Admin)                                             │
│   ─────────────────────────────                                             │
│                                                                              │
│   1. approval_modules         → Daftar modul yang pakai approval            │
│      Contoh: transactions, bookings, invoices                               │
│                                                                              │
│   2. approval_flow_templates  → Aturan approval per modul                   │
│      Contoh: "Invoice pakai uppline + threshold"                            │
│                                                                              │
│   3. approval_flow_details    → Siapa saja approver master flow             │
│      Contoh: Employee D (10jt), Employee E (100jt), Employee F (1M)                     │
│                                                                              │
│   4. users.uppline_id         → Struktur atasan setiap user                 │
│      Contoh: Employee A → atasan Employee B → atasan Employee C                         │
│                                                                              │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│   📝 SAAT USER SUBMIT REQUEST                                               │
│   ───────────────────────────                                               │
│                                                                              │
│   5. approval_requests        → 1 record dibuat (header)                    │
│      Menyimpan: siapa submit, status, phase saat ini, snapshot konfigurasi  │
│                                                                              │
│   6. approval_request_details → N record dibuat (approver list)             │
│      Menyimpan: daftar semua approver (uppline + master) dan statusnya      │
│                                                                              │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│   ✅ SAAT APPROVER APPROVE/REJECT                                           │
│   ────────────────────────────                                              │
│                                                                              │
│   7. approval_request_details → Update status per level                     │
│                                                                              │
│   8. approval_requests        → Update current_level, current_phase,        │
│                                  dan status jika selesai                    │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 3.2 ERD dengan MySQL Types

```sql
-- ═══════════════════════════════════════════════════════════════════════════
--                          MASTER TABLES (Setup 1x)
-- ═══════════════════════════════════════════════════════════════════════════

-- Tabel employee dengan struktur atasan
CREATE TABLE employee (
    id              INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name            VARCHAR(100) NOT NULL,
    email           VARCHAR(100) NOT NULL UNIQUE,
    uppline_id      INT UNSIGNED NULL,      -- FK ke employee.id (atasan langsung)
    is_active       TINYINT(1) DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (uppline_id) REFERENCES employee(id) ON DELETE SET NULL
);

-- Modul yang pakai approval
CREATE TABLE approval_modules (
    id              INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    module_name     VARCHAR(50) NOT NULL,   -- 'transactions', 'invoices', 'bookings'
    table_name      VARCHAR(50) NOT NULL,   -- nama tabel asli
    is_active       TINYINT(1) DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Template/aturan approval per modul
CREATE TABLE approval_flow_templates (
    id                  INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    module_id           INT UNSIGNED NOT NULL,      -- FK ke approval_modules
    template_name       VARCHAR(100) NOT NULL,
    use_uppline_chain   TINYINT(1) DEFAULT 0,       -- TRUE = pakai uppline chain
    use_threshold       TINYINT(1) DEFAULT 0,       -- TRUE = filter by nominal
    condition_field     VARCHAR(50) NULL,           -- 'amount', 'total', dll
    priority            INT DEFAULT 1,
    is_active           TINYINT(1) DEFAULT 1,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (module_id) REFERENCES approval_modules(id)
);

-- Detail approver untuk setiap template
CREATE TABLE approval_flow_details (
    id                  INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    template_id         INT UNSIGNED NOT NULL,      -- FK ke approval_flow_templates
    level_sequence      INT NOT NULL,               -- urutan: 1, 2, 3...
    employee_id             INT UNSIGNED NOT NULL,      -- FK ke employee (approver)
    threshold_amount    DECIMAL(15,2) NULL,         -- batas nominal (jika threshold)
    is_required         TINYINT(1) DEFAULT 1,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (template_id) REFERENCES approval_flow_templates(id),
    FOREIGN KEY (employee_id) REFERENCES employee(id)
);

-- ═══════════════════════════════════════════════════════════════════════════
--                    TRANSACTIONAL TABLES (Per Request)
-- ═══════════════════════════════════════════════════════════════════════════

-- Header request approval
CREATE TABLE approval_requests (
    id                  INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    module_id           INT UNSIGNED NOT NULL,      -- FK ke approval_modules
    reference_id        INT UNSIGNED NOT NULL,      -- ID dari tabel asli (invoice.id)
    reference_number    VARCHAR(50) NOT NULL,       -- nomor dokumen (INV-001)
    template_id         INT UNSIGNED NOT NULL,      -- FK ke approval_flow_templates
    template_snapshot   JSON NOT NULL,              -- backup config saat submit
    status              ENUM('draft','pending','approved','rejected') DEFAULT 'draft',
    current_phase       ENUM('uppline','master_flow') DEFAULT 'uppline',
    current_level       INT DEFAULT 1,              -- level saat ini dalam phase
    total_levels        INT NOT NULL,               -- total semua level
    requester_id        INT UNSIGNED NOT NULL,      -- FK ke users
    requested_at        TIMESTAMP NULL,
    completed_at        TIMESTAMP NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (module_id) REFERENCES approval_modules(id),
    FOREIGN KEY (template_id) REFERENCES approval_flow_templates(id),
    FOREIGN KEY (requester_id) REFERENCES employee(id)
);

-- Detail approver per request (snapshot)
CREATE TABLE approval_request_details (
    id                  INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    request_id          INT UNSIGNED NOT NULL,      -- FK ke approval_requests
    phase               ENUM('uppline','master_flow') NOT NULL,
    level_sequence      INT NOT NULL,               -- urutan dalam phase
    employee_id             INT UNSIGNED NOT NULL,      -- FK ke users (approver)
    user_name           VARCHAR(100) NOT NULL,      -- snapshot nama
    status              ENUM('pending','approved','rejected') DEFAULT 'pending',
    approved_at         TIMESTAMP NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (request_id) REFERENCES approval_requests(id),
    FOREIGN KEY (employee_id) REFERENCES employee(id)
);
```

### 3.3 Penjelasan Singkat

| Tabel                      | Kapan Dipakai | Isi                                                  |
| -------------------------- | ------------- | ---------------------------------------------------- |
| `employee`                 | Setup awal    | Data user + siapa atasannya (`uppline_id`)           |
| `approval_modules`         | Setup awal    | Modul apa saja yang pakai approval                   |
| `approval_flow_templates`  | Setup awal    | Aturan: pakai uppline? pakai threshold?              |
| `approval_flow_details`    | Setup awal    | Daftar approver master flow + nominal threshold      |
| `approval_requests`        | Per submit    | Header: siapa submit, status, level saat ini         |
| `approval_request_details` | Per submit    | Detail: daftar semua approver + status masing-masing |

---

## 4. FLOW PROSES

### 4.1 Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                 USER SUBMIT REQUEST                          │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ SISTEM: BUILD APPROVAL FLOW                                  │
│                                                              │
│ 1. Build Uppline Chain (jika use_uppline_chain=TRUE)        │
│    → Recursive dari requester.uppline_id sampai NULL        │
│                                                              │
│ 2. Get Master Flow Details (dari template)                  │
│    → Jika use_threshold=TRUE: filter berdasarkan amount     │
│    → Jika use_threshold=FALSE: ambil semua level            │
│                                                              │
│ 3. Combine: Uppline Chain + Master Flow                     │
│ 4. Create approval_requests + approval_request_details      │
│ 5. Notify first approver                                    │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ PHASE 1: UPPLINE CHAIN                                       │
│ Approver 1 → Approver 2 → ... (sampai selesai)              │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ PHASE 2: MASTER FLOW                                         │
│ Approver 1 → Approver 2 → ... (sampai selesai)              │
└─────────────────────────────────────────────────────────────┘
                            ↓
                    ✅ FULLY APPROVED
```

### 4.2 Status Flow

```
draft → pending → approved
                ↓
              rejected
```

### 4.3 Phase Flow

```
current_phase='uppline' → current_phase='master_flow' → approved
```

---

## 5. PANDUAN KONFIGURASI

### 5.1 Setup Struktur Organisasi (Uppline)

```sql
-- Employee A (staff) → atasan: Employee B
UPDATE users SET uppline_id = 102 WHERE id = 101;

-- Employee B (supervisor) → atasan: Employee C
UPDATE users SET uppline_id = 103 WHERE id = 102;

-- Employee C (manager) → tidak ada atasan (top)
UPDATE users SET uppline_id = NULL WHERE id = 103;
```

### 5.2 Konfigurasi Template dengan Threshold (Amount-Based)

```sql
-- Template Invoice dengan uppline chain + threshold
INSERT INTO approval_flow_templates
(module_id, template_name, use_uppline_chain, use_threshold, condition_field, priority, is_active)
VALUES
(3, 'Invoice Approval', TRUE, TRUE, 'total', 1, TRUE);

-- Master Flow dengan threshold
INSERT INTO approval_flow_details
(template_id, level_sequence, employee_id, threshold_amount, is_required) VALUES
(1, 1, 104, 10000000, TRUE),   -- Employee D: sampai 10 juta
(1, 2, 105, 100000000, TRUE),  -- Employee E: sampai 100 juta
(1, 3, 106, 1000000000, TRUE); -- Employee F: sampai 1 miliar
```

**Hasil untuk pengajuan 90 juta:**

-   Uppline: Employee B → Employee C
-   Master: Employee D → Employee E (Employee F di-skip karena 90jt < 1M)

### 5.3 Konfigurasi Template tanpa Threshold (All-Levels)

```sql
-- Template Booking tanpa threshold (semua level wajib)
INSERT INTO approval_flow_templates
(module_id, template_name, use_uppline_chain, use_threshold, priority, is_active)
VALUES
(2, 'Booking Approval', TRUE, FALSE, 1, TRUE);

-- Master Flow (semua wajib)
INSERT INTO approval_flow_details
(template_id, level_sequence, employee_id, is_required) VALUES
(2, 1, 107, TRUE),  -- Employee G
(2, 2, 108, TRUE),  -- Employee H
(2, 3, 109, TRUE);  -- Employee I
```

**Hasil:**

-   Uppline: Employee B → Employee C
-   Master: Employee G → Employee H → Employee I (semua wajib)

### 5.4 Konfigurasi Tanpa Uppline Chain

```sql
-- Template hanya Master Flow (tanpa uppline)
INSERT INTO approval_flow_templates
(module_id, template_name, use_uppline_chain, use_threshold, priority, is_active)
VALUES
(1, 'Transaction Approval', FALSE, TRUE, 1, TRUE);
```

### 5.5 Kombinasi Konfigurasi

| `use_uppline_chain` | `use_threshold` | Hasil Flow                                       |
| ------------------- | --------------- | ------------------------------------------------ |
| TRUE                | TRUE            | Uppline Chain → Master Flow (threshold-filtered) |
| TRUE                | FALSE           | Uppline Chain → Master Flow (all levels)         |
| FALSE               | TRUE            | Master Flow saja (threshold-filtered)            |
| FALSE               | FALSE           | Master Flow saja (all levels)                    |

---

## 📌 KESIMPULAN

Sistem approval ini mendukung:

-   ✅ **Uppline Chain**: Approval dari atasan langsung secara otomatis
-   ✅ **Threshold-Based**: Filter approver berdasarkan nominal
-   ✅ **All-Levels**: Semua level wajib approve
-   ✅ **Configurable**: Kombinasi fleksibel per template
-   ✅ **Two-Phase**: Uppline dulu, baru Master Flow
