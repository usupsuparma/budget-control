# Issue: Master Data Employment Dynamic Synchronization (AJAX Refactor)

## 1. Masalah
Saat ini, menu **Settings -> Employment** (URL: `/master-data`) menggunakan data statis yang di-load sekali di `MasterController::index`. Jika user menambahkan data baru (misal: Division baru), data tersebut tidak muncul di dropdown (misal: saat tambah Department) tanpa me-restart/refresh halaman secara penuh.

## 2. Tujuan
Mengimplementasikan sistem sinkronisasi data master berbasis AJAX sehingga setiap perubahan data (Create/Update/Delete) pada satu tab akan secara otomatis memperbarui list dropdown di tab lainnya tanpa reload halaman.

## 3. Standar Arsitektur (MANDATORY - Refer @GEMINI.md)
*   **Service Layer:** Gunakan Interface + Implementation.
*   **Binding:** Daftarkan service di `CustomServiceProvider.php`.
*   **Validation:** Gunakan FormRequest (`php artisan make:request`).
*   **Choices.js:** Setiap `<select>` wajib menggunakan instance individu Choices.js.
*   **AJAX:** Gunakan standar JSON response `{ success: true, data: ... }`.
*   **Testing:** Sertakan PHPUnit test untuk service method yang baru/direfactor.

## 4. Rencana Implementasi

### A. Backend (Senior/Cheaper AI Tasks)
1.  **Refactor MasterDataService:**
    *   Pastikan ada method untuk mengambil list `JobPosition`, `JobLevel`, `Director`, `Division`, `Department`, dan `Section` yang berstatus 'Active'.
2.  **API Endpoints:**
    *   Buat endpoint di `web.php` (misal: `GET /master-data/options`) yang mengembalikan data JSON untuk semua list master di atas.
    *   Alternatif: Endpoint individual per entitas jika data terlalu besar (misal: `GET /division/options`).
3.  **Controllers:**
    *   Update `MasterController::index` untuk tidak lagi mem-pass data list master yang bersifat dinamis (biarkan view kosong atau hanya inisialisasi awal).

### B. Frontend (Junior/AI Tasks)
1.  **Global Data Manager (JavaScript):**
    *   Buat fungsi `refreshMasterOptions()` yang memanggil API endpoint di atas.
    *   Simpan hasilnya dalam variabel JavaScript global (misal: `window.masterData`).
2.  **Dynamic Select Rendering:**
    *   Buat fungsi utilitas (misal: `populateSelect(elementId, data, selectedValue)`) yang:
        1. Menghapus isi select lama.
        2. Menambahkan option baru dari data JSON.
        3. Menghancurkan (destroy) dan menginisialisasi ulang instance **Choices.js**.
3.  **Integration with Modals:**
    *   Pada setiap event `success` dari AJAX Create/Update (misal di `JobPosition`, `Division`, dll):
        1. Panggil `refreshMasterOptions()`.
        2. Jalankan `populateSelect()` untuk semua dropdown yang relevan di tab lain.
4.  **Choices.js Implementation:**
    *   Ikuti standar di `documentasi/CHOICES_JS_STANDARD.md`. Pastikan setiap select memiliki instance unik yang bisa di-update.

## 5. Langkah Validasi
1.  Buka tab "Division", tambah satu divisi baru.
2.  Langsung buka tab "Department", buka modal "Add Department".
3.  Pastikan divisi baru yang tadi dibuat sudah muncul di dropdown "Division" tanpa refresh halaman.
4.  Lakukan hal yang sama untuk relasi lainnya (Director -> Division, Department -> Section).
5.  Jalankan unit test: `php artisan test`.

## 6. File Terkait
*   `app/Http/Controllers/MasterController.php`
*   `app/Services/MasterDataService/`
*   `resources/views/pages/settings/Settings.blade.php`
*   `resources/views/pages/settings/*.blade.php` (Partial tabs)
*   `routes/web.php`
