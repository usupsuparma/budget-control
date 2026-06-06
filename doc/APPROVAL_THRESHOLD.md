# Technical Implementation Document: Dynamic Upline Approval with Threshold

**Module:** Budget Control / HRIS Approval System
**Author:** System Analyst / Engineering Team
**Status:** Ready for Implementation

## 1. Ringkasan Fitur (Executive Summary)

Pembaruan ini bertujuan untuk mengimplementasikan sistem *Authority Limit* (Threshold) pada rantai persetujuan atasan (*Upline Approval*). Sebelumnya, sistem upline bersifat statis. Dengan pembaruan ini, atasan dalam rantai hierarki hanya akan dilibatkan dalam proses *approval* jika nominal pengajuan (request amount) memenuhi atau melebihi batas *threshold* yang ditetapkan untuk level jabatan mereka.

## 2. Perubahan Skema Database (Database Schema Migration)

Kita perlu menambahkan kolom `threshold_amount` pada tabel `approval_flow_uppline_configs`.

**Target Table:** `approval_flow_uppline_configs`

| Nama Kolom Baru | Tipe Data | Nullable | Default | Keterangan |
| --- | --- | --- | --- | --- |
| `threshold_amount` | `bigint unsigned` | YES | `0` | Batas minimum nominal agar level ini dilibatkan. |

**SQL Execution:**

```sql
ALTER TABLE approval_flow_uppline_configs
ADD COLUMN threshold_amount bigint unsigned NULL DEFAULT 0 AFTER job_level_name;

```

*(Jika menggunakan Laravel Migration)*:

```php
Schema::table('approval_flow_uppline_configs', function (Blueprint $table) {
    $table->unsignedBigInteger('threshold_amount')->nullable()->default(0)->after('job_level_name');
});

```

## 3. Penyesuaian Model Aplikasi (Model Layer)

Pastikan untuk mendaftarkan kolom baru pada model *ORM* agar dapat diisi (mass-assignable) saat admin mengatur *template*.

**File:** `app/Models/ApprovalFlowUpplineConfig.php`

```php
class ApprovalFlowUpplineConfig extends Model
{
    // ...
    protected $fillable = [
        'template_id',
        'division_id',
        'step_sequence',
        'job_level_name',
        'threshold_amount', // Field baru ditambahkan
    ];
}

```

## 4. Logika Bisnis & Generasi Data (Service / Action Layer)

Saat sebuah `approval_requests` baru dibuat, sistem akan melakukan iterasi pada konfigurasi upline untuk membuat `approval_request_details`. Logika *bypass* (skip) harus ditambahkan di sini.

**Algoritma Evaluasi Upline:**

1. Ambil nilai pengajuan (`$requestAmount`).
2. Cek apakah template menggunakan fitur threshold (`$template->use_threshold == 1`).
3. Iterasi setiap urutan atasan dari `approval_flow_uppline_configs`.
4. Jika *use_threshold* aktif, evaluasi: `Apakah $requestAmount >= $config->threshold_amount?`
* Jika **YA**: Masukkan atasan ke dalam `approval_request_details`.
* Jika **TIDAK**: Lewati (*continue/skip*) atasan ini.



**Contoh Implementasi Service:**

```php
public function generateUplineDetails($requestId, $templateId, $requestAmount, $requesterEmploymentId)
{
    $template = ApprovalFlowTemplate::find($templateId);
    
    if (!$template || !$template->use_uppline_chain) {
        return false;
    }

    $uplineConfigs = ApprovalFlowUpplineConfig::where('template_id', $templateId)
                        ->orderBy('step_sequence', 'asc')
                        ->get();

    $sequenceCounter = 1;

    foreach ($uplineConfigs as $config) {
        
        // Pengecekan Threshold
        if ($template->use_threshold) {
            $threshold = $config->threshold_amount ?? 0;
            if ($requestAmount < $threshold) {
                // Nominal terlalu kecil untuk level atasan ini, SKIP.
                continue; 
            }
        }

        // Dapatkan data employment_id atasan berdasarkan hierarchy/division/job_level
        $uplineEmployment = $this->findUplineEmployment($requesterEmploymentId, $config);

        if ($uplineEmployment) {
            ApprovalRequestDetail::create([
                'request_id'     => $requestId,
                'phase'          => 'uppline',
                'level_sequence' => $sequenceCounter,
                'employment_id'  => $uplineEmployment->id,
                'employment_name'=> $uplineEmployment->name,
                'status'         => 'pending',
            ]);
            $sequenceCounter++;
        }
    }
    
    // Update total_levels pada approval_requests jika diperlukan
    // ...
}

```

## 5. Skenario Pengujian (Quality Assurance / QA)

Untuk memastikan logika berjalan dengan benar, tim QA harus menjalankan pengujian berikut:

| Skenario (Test Case) | Input (Nominal) | Ekspektasi Hasil (Upline Terlibat) | Status |
| --- | --- | --- | --- |
| **TC-01:** Template tidak pakai threshold | Rp 1.000.000 | SPV, Manager, General Manager (Semua) | ⬜ |
| **TC-02:** Pengajuan di bawah batas Manager (Batas: 5jt) | Rp 2.500.000 | Hanya SPV | ⬜ |
| **TC-03:** Pengajuan di atas Manager, di bawah GM (Batas GM: 20jt) | Rp 10.000.000 | SPV -> Manager | ⬜ |
| **TC-04:** Pengajuan sangat besar | Rp 50.000.000 | SPV -> Manager -> General Manager | ⬜ |
