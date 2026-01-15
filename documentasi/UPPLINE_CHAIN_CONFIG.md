# Uppline Chain Configuration Guide

## Overview
Uppline Chain Configuration memungkinkan Anda mengatur level jabatan yang diperlukan untuk approval chain berdasarkan hierarki atasan (uppline). Konfigurasi ini dapat dibuat secara **default** untuk semua divisi atau **spesifik** untuk divisi tertentu.

## Cara Penggunaan

### 1. Mengaktifkan Uppline Chain pada Template

Saat membuat atau mengedit **Approval Flow Template**:

1. Buka menu **Master Approval** → tab **Templates**
2. Klik **"Tambah Template"** atau edit template existing
3. Centang checkbox **"Use Uppline Chain"**
4. Section **"Uppline Chain Configuration"** akan muncul

### 2. Menambahkan Level Configuration

Setelah checkbox **Use Uppline Chain** dicentang:

1. Klik tombol **"Add Level Configuration"**
2. Isi form:
   - **Division**: Pilih divisi spesifik atau kosongkan untuk default
   - **Step Sequence**: Urutan approval (1, 2, 3, dst)
   - **Job Level Name**: Pilih dari dropdown (Director, Division, Department, Section)
3. Klik **"Simpan"**

### 3. Contoh Konfigurasi

#### Konfigurasi Default (Berlaku untuk semua divisi)
```
Division: Default (All Divisions)
- Step 1: Section
- Step 2: Department
- Step 3: Division
```

#### Konfigurasi Spesifik IT Division (Override default)
```
Division: IT Division
- Step 1: Section
- Step 2: Department
- Step 3: Division
- Step 4: Director
```

#### Konfigurasi Spesifik Finance Division
```
Division: Finance Division
- Step 1: Department
- Step 2: Division
```

## Logika Prioritas

Sistem menggunakan logika **"Specific Division First, Default Later"**:

1. **Jika user dari divisi yang memiliki konfigurasi khusus** → Gunakan konfigurasi khusus divisi tersebut
2. **Jika user dari divisi yang tidak memiliki konfigurasi khusus** → Gunakan konfigurasi default

### Contoh Skenario

**Setup:**
- Default Config: Section → Department → Division
- IT Division Config: Section → Department → Division → Director

**Hasil:**
- User dari IT Division: Approver 4 level (sampai Director)
- User dari Finance Division: Approver 3 level (sampai Division) - pakai default
- User dari HR Division: Approver 3 level (sampai Division) - pakai default

## Cara Kerja di Runtime

Ketika user submit request:

1. Sistem identifikasi divisi user
2. Sistem cek apakah ada konfigurasi khusus untuk divisi tersebut
3. Sistem ambil konfigurasi (spesifik atau default)
4. Sistem build uppline chain berdasarkan:
   - Hierarki atasan user (`uppline_id`)
   - Job level yang terdaftar di konfigurasi
5. Hanya atasan yang job level-nya sesuai konfigurasi yang akan masuk approval chain

### Contoh Flow

**User:** Staff IT (uppline: Supervisor IT → Section IT → Dept IT → Div IT → Director IT)

**Config IT Division:**
- Step 1: Section
- Step 2: Department
- Step 3: Division
- Step 4: Director

**Hasil Approval Chain:**
1. Section IT (Level: Section) ✓
2. Department IT (Level: Department) ✓
3. Division IT (Level: Division) ✓
4. Director IT (Level: Director) ✓

**Note:** Supervisor IT tidak masuk karena tidak terdaftar di konfigurasi

## Best Practices

1. **Mulai dengan Default Config**
   - Buat konfigurasi default terlebih dahulu untuk aturan umum

2. **Buat Specific Config hanya jika perlu**
   - Hanya buat konfigurasi spesifik untuk divisi yang butuh aturan berbeda

3. **Gunakan Job Level yang Konsisten**
   - Pilihan: **Director, Division, Department, Section**
   - Urutan hierarki: Section (bawah) → Department → Division → Director (atas)

4. **Test dengan User dari Berbagai Divisi**
   - Pastikan logic override bekerja dengan benar

## Troubleshooting

### Konfigurasi tidak muncul
- Pastikan checkbox "Use Uppline Chain" sudah dicentang
- Simpan template terlebih dahulu sebelum menambah konfigurasi

### Approval chain tidak sesuai
- Cek apakah Job Level Name di konfigurasi sesuai dengan data master
- Cek apakah uppline_id user sudah terisi dengan benar
- Cek apakah ada konfigurasi spesifik yang meng-override default

### Duplicate step sequence error
- Tidak boleh ada 2 konfigurasi dengan step sequence sama untuk divisi yang sama
- Jika divisi spesifik, cek per divisi
- Jika default, cek konfigurasi default

## Related Documentation

- [APPROVAL_SYSTEM.md](APPROVAL_SYSTEM.md) - Overview sistem approval
- [APPROVAL_UPLINE_CHAINING.md](APPROVAL_UPLINE_CHAINING.md) - Technical specification
