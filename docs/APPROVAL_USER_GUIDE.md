# 📖 PANDUAN PENGGUNAAN MASTER APPROVAL

## Daftar Isi

1. [Pengenalan](#1-pengenalan)
2. [Mengakses Master Approval](#2-mengakses-master-approval)
3. [Tab Modules](#3-tab-modules)
4. [Tab Templates](#4-tab-templates)
5. [Tab Flow Details](#5-tab-flow-details)
6. [Contoh Konfigurasi Lengkap](#6-contoh-konfigurasi-lengkap)
7. [FAQ & Troubleshooting](#7-faq--troubleshooting)

---

## 1. Pengenalan

### Apa itu Master Approval?

Master Approval adalah fitur untuk mengatur **siapa yang harus menyetujui** pengajuan dalam sistem (Budget, Reimbursement, dll).

### Istilah Penting

| Istilah           | Arti                                         |
| ----------------- | -------------------------------------------- |
| **Module**        | Fitur/menu yang membutuhkan approval         |
| **Template**      | Aturan approval untuk module tertentu        |
| **Flow Detail**   | Daftar approver dalam urutan tertentu        |
| **Threshold**     | Batas nominal yang menentukan level approval |
| **Uppline Chain** | Approval mengikuti jalur atasan langsung     |

---

## 2. Mengakses Master Approval

1. Login ke aplikasi
2. Buka menu **Approval** → **Master Approval**
3. Anda akan melihat 3 tab:
    - 📦 **Modules** - Daftar fitur yang butuh approval
    - 📋 **Templates** - Aturan approval
    - 👥 **Flow Details** - Daftar approver

---

## 3. Tab Modules

### Fungsi

Mendaftarkan fitur/menu apa saja yang memerlukan proses approval.

### Field yang harus diisi

| Field           | Keterangan                  | Contoh                          |
| --------------- | --------------------------- | ------------------------------- |
| **Module Name** | Nama modul (unik)           | `Budget`, `Reimbursement`, `PO` |
| **Table Name**  | Nama tabel database terkait | `workplan_budget_items`         |
| **Active**      | Aktifkan/nonaktifkan modul  | ✅ Aktif                        |

### Cara Menambah Module

1. Klik tombol **+ Tambah Module**
2. Isi Module Name: `Budget`
3. Isi Table Name: `workplan_budget_items`
4. Pastikan toggle **Active** menyala
5. Klik **Simpan**

> ⚠️ **Catatan**: Table Name harus sesuai dengan nama tabel di database.

---

## 4. Tab Templates

### Fungsi

Mengatur **bagaimana** approval bekerja untuk setiap module.

### Field yang harus diisi

| Field                 | Keterangan                       | Contoh                     |
| --------------------- | -------------------------------- | -------------------------- |
| **Module**            | Pilih module yang sudah dibuat   | Budget                     |
| **Template Name**     | Nama template (bebas)            | `Budget Approval Standard` |
| **Condition Field**   | Nama kolom untuk threshold       | `total`                    |
| **Priority**          | Urutan prioritas (1 = tertinggi) | `1`                        |
| **Use Uppline Chain** | Approval ke atasan langsung dulu | ✅/❌                      |
| **Use Threshold**     | Filter berdasarkan nominal       | ✅/❌                      |
| **Active**            | Aktifkan template                | ✅ Aktif                   |

### Penjelasan Opsi Toggle

#### Use Uppline Chain

-   **ON** → Sebelum ke approver di Flow Details, pengajuan akan dikirim ke atasan langsung dulu
-   **OFF** → Langsung ke approver di Flow Details

```
Contoh (ON):
User Submit → Atasan Langsung → Manager → Direktur (Flow Details)

Contoh (OFF):
User Submit → Manager → Direktur (Flow Details)
```

#### Use Threshold

-   **ON** → Approver yang aktif tergantung nominal pengajuan
-   **OFF** → Semua approver di Flow Details harus approve

```
Contoh (ON, pengajuan 75jt):
Level 1: Manager (threshold 0)       → AKTIF
Level 2: Direktur (threshold 50jt)   → AKTIF (75jt > 50jt)
Level 3: Komisaris (threshold 200jt) → SKIP (75jt < 200jt)

Contoh (OFF):
Level 1: Manager    → WAJIB
Level 2: Direktur   → WAJIB
Level 3: Komisaris  → WAJIB
```

### Kombinasi Konfigurasi

| Uppline Chain | Use Threshold | Hasil                                                   |
| ------------- | ------------- | ------------------------------------------------------- |
| ✅ ON         | ✅ ON         | Atasan langsung dulu → Flow Details (filter by nominal) |
| ✅ ON         | ❌ OFF        | Atasan langsung dulu → Flow Details (semua level)       |
| ❌ OFF        | ✅ ON         | Langsung Flow Details (filter by nominal)               |
| ❌ OFF        | ❌ OFF        | Langsung Flow Details (semua level)                     |

---

## 5. Tab Flow Details

### Fungsi

Menentukan **siapa saja** yang menjadi approver dan urutannya. Pada tab ini, daftar ditampilkan dalam bentuk accordion:

-   Setiap **Template** memiliki panel expand/collapse sendiri untuk melihat approver.
-   **LPJ Master Approvers** tampil sebagai panel accordion terakhir di bagian paling bawah dalam card yang sama.

### Cara Menggunakan

1. Buka panel template yang ingin diatur
2. Klik **+ Tambah Approver**
3. Isi form:

| Field                   | Keterangan                   | Contoh                   |
| ----------------------- | ---------------------------- | ------------------------ |
| **Level Sequence**      | Urutan approval (1, 2, 3...) | `1`                      |
| **Employee (Approver)** | Pilih karyawan               | `Budi - Finance Manager` |
| **Threshold Amount**    | Batas nominal (opsional)     | `50.000.000`             |
| **Required**            | Wajib approve?               | ✅ Wajib                 |

### LPJ Master Approvers

-   Buka panel **LPJ Master Approvers** yang berada paling bawah pada accordion.
-   Klik **+ Tambah Approver** untuk menambah approver LPJ.
-   Data LPJ menggunakan pola tampilan yang sama seperti Flow Details template: expand/collapse, daftar approver, dan aksi edit/hapus di dalam panel.

### Penjelasan Threshold Amount

-   **Kosong / 0** → Approver ini aktif untuk semua nominal
-   **50.000.000** → Approver ini aktif jika nominal pengajuan ≥ threshold level sebelumnya DAN ≤ 50jt ATAU perlu level lebih tinggi

**Contoh Setup:**

| Level | Approver  | Threshold      |
| ----- | --------- | -------------- |
| 1     | Manager   | Rp 0           |
| 2     | Direktur  | Rp 50.000.000  |
| 3     | Komisaris | Rp 200.000.000 |

**Hasilnya:**

-   Pengajuan **Rp 30jt** → Manager saja
-   Pengajuan **Rp 75jt** → Manager + Direktur
-   Pengajuan **Rp 300jt** → Manager + Direktur + Komisaris

---

## 6. Contoh Konfigurasi Lengkap

### Skenario

Buat approval untuk Budget dengan ketentuan:

-   Nominal ≤ 50 juta: Approval oleh Finance Manager
-   Nominal > 50 juta: Approval oleh Finance Manager + Direktur

### Langkah-langkah

#### Step 1: Tambah Module

```
Module Name : Budget
Table Name  : workplan_budget_items
Active      : ✅
```

#### Step 2: Tambah Template

```
Module          : Budget
Template Name   : Budget Approval Standard
Condition Field : total
Priority        : 1
Use Uppline     : ❌ OFF
Use Threshold   : ✅ ON
Active          : ✅
```

#### Step 3: Tambah Flow Details

Pilih template "Budget Approval Standard", lalu tambahkan:

**Approver 1:**

```
Level Sequence    : 1
Employee          : Budi - Finance Manager
Threshold Amount  : (kosongkan)
Required          : ✅
```

**Approver 2:**

```
Level Sequence    : 2
Employee          : Pak Ahmad - Direktur
Threshold Amount  : 50.000.000
Required          : ✅
```

### Hasil

-   Submit budget Rp 30jt → Hanya perlu approval **Finance Manager**
-   Submit budget Rp 100jt → Perlu approval **Finance Manager** + **Direktur**

---

## 7. FAQ & Troubleshooting

### Q: Dropdown Employee kosong?

**A:** Pastikan data Employment sudah terisi dan terhubung dengan data Employee.

### Q: Error "is_active must be true or false"?

**A:** Sudah diperbaiki. Refresh halaman dan coba lagi.

### Q: Bagaimana urutan approval bekerja?

**A:** Approval berjalan berurutan (sequential):

1. Level 1 approve dulu
2. Setelah Level 1 approved, baru Level 2 bisa approve
3. Dan seterusnya

### Q: Apa bedanya Module dan Template?

**A:**

-   **Module** = "Fitur APA" (Budget, PO, dll)
-   **Template** = "ATURAN bagaimana" (pakai threshold? pakai uppline?)

### Q: Bisa 1 Module punya banyak Template?

**A:** Bisa. Gunakan **Priority** untuk menentukan template mana yang dicek duluan.

### Q: Bagaimana jika approver cuti/tidak tersedia?

**A:** Saat ini sistem tidak otomatis skip. Perlu koordinasi manual atau update Flow Details.

---

## 📞 Bantuan

Jika ada pertanyaan atau kendala, hubungi tim IT atau administrator sistem.

---

_Dokumentasi ini dibuat untuk memudahkan penggunaan Master Approval Setup._
_Terakhir diperbarui: Januari 2026_
