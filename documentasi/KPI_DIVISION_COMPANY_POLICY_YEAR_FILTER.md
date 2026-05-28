# KPI Division: Company Policy Year Filter

## Ringkasan Perubahan

Pada modal Add New KPI Division, dropdown Company Policy sekarang hanya menampilkan data policy yang memiliki tahun dokumen sama dengan tahun yang dipilih pada field Year.

Perubahan ini berlaku untuk:
- Mode Add: saat user membuka modal dan saat user mengganti Year.
- Mode Edit: saat data KPI Division dibuka, opsi Company Policy diisi ulang berdasarkan year dari data yang diedit, lalu memilih policy yang tersimpan.

## Perilaku Baru

1. Field Year menjadi sumber filter utama untuk Company Policy.
2. Dropdown Company Policy tidak lagi menampilkan seluruh tahun sekaligus.
3. Jika tidak ada policy untuk tahun terpilih, dropdown menampilkan placeholder notifikasi bahwa data policy untuk tahun tersebut tidak tersedia.

## File Terdampak

- resources/views/pages/kpi/division_rev1.blade.php

## Detail Implementasi

1. Opsi Company Policy di Blade diberi atribut data-year dari relasi dokumen policy.
2. Seluruh opsi policy disalin ke struktur data JavaScript saat halaman dimuat.
3. Ditambahkan fungsi renderCompanyPolicyOptionsByYear(selectedYear, selectedPolicyId = '') untuk:
	- memfilter opsi berdasarkan tahun,
	- merender ulang option dropdown,
	- menjaga selected value saat mode edit.
4. Fungsi filter dipanggil saat:
	- inisialisasi halaman,
	- perubahan field Year,
	- klik tombol Add New KPI Division,
	- proses load data mode Edit.

## Catatan Operasional dan Uji

1. Pastikan data Company Policy memiliki relasi dokumen dengan nilai tahun yang terisi.
2. Uji manual minimal:
	- pilih tahun A, pastikan policy yang muncul hanya tahun A,
	- ganti ke tahun B, pastikan opsi ter-update,
	- buka mode Edit data existing dan pastikan policy lama tetap terpilih jika cocok dengan tahun.
3. Jika belum ada policy untuk tahun terpilih, user harus memilih tahun lain atau menyiapkan data policy tahun tersebut terlebih dahulu.
