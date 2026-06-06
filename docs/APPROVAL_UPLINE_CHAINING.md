# Technical Spec: Dynamic Upline Approval Chaining with Conditional Depth

## 1. Context & Objective

Implementasi logika **Approval Matrix Generator** dinamis. Sistem harus men-generate daftar approver secara otomatis berdasarkan hierarki atasan (*upline*) karyawan, namun difilter oleh konfigurasi template.

**Requirement Utama:**

1. **Chaining:** Mengambil atasan secara rekursif (Employee -> Supervisor -> Manager -> ...).
2. **Intersection:** Hanya mengambil atasan yang `job_level_id`-nya terdaftar di konfigurasi template.
3. **Conditional Depth (Division Override):** Logika prioritas konfigurasi.
* Jika Divisi requester memiliki konfigurasi spesifik (misal: IT harus sampai Director), gunakan itu.
* Jika tidak, gunakan konfigurasi default (misal: Finance cukup sampai Dept Head).



## 2. Data Structure Reference

### Table: `approval_flow_uppline_configs` (Configuration Source)

Pastikan tabel ini ada di database sebelum eksekusi logic.

* `template_id` (FK): Link ke template approval.
* `division_id` (FK/Nullable): **Kunci Logika Override**. Jika NULL = Default Config. Jika Terisi = Specific Config.
* `job_level_id`: Level jabatan yang dibutuhkan (misal: 3=Section, 4=Dept, 5=Div).
* `step_sequence`: Urutan approval.

### Table: `employment` (Hierarchy Source)

* `id`: Employee ID.
* `uppline_id`: ID atasan (Recursive Pointer).
* `job_level_id`: ID level jabatan karyawan tersebut.

---

## 3. Implementation Logic (Step-by-Step)

AI Agent harus mengimplementasikan method (misalnya: `generateUplineApprovers`) dengan alur berikut:

### Step 1: Identify Requester Context

Dapatkan data `division_id` dari karyawan yang membuat request.

* *Input:* `$requesterId`
* *Process:* Load data karyawan. Pastikan sistem bisa me-resolve karyawan ini milik Divisi mana (via relasi `organization_id` atau logic existing aplikasi).
* *Output:* `$userDivisionId` (int|null).

### Step 2: Retrieve Approval Configuration (Priority Logic)

Sistem harus mengambil daftar Job Level yang dibutuhkan dengan logika **"Specific Division First, Default Later"**.

**Logic Rule:**

> "Ambil konfigurasi untuk Divisi user ini. Jika kosong/tidak ada, ambil konfigurasi yang division_id-nya NULL."

**SQL Implementation Strategy:**

```sql
SELECT job_level_id, step_sequence
FROM approval_flow_uppline_configs
WHERE template_id = :templateId
AND (
    -- Case A: Cocok dengan divisi user
    division_id = :userDivisionId
    OR
    -- Case B: Default config (hanya jika Case A tidak ditemukan)
    (
        division_id IS NULL
        AND NOT EXISTS (
            SELECT 1
            FROM approval_flow_uppline_configs specific
            WHERE specific.template_id = :templateId
            AND specific.division_id = :userDivisionId
        )
    )
)
ORDER BY step_sequence ASC;

```

* *Output:* Array of allowed Job Level IDs (e.g., `[3, 4, 5]` for IT, or `[3, 4]` for Finance).

### Step 3: Fetch Recursive Upline Hierarchy

Ambil seluruh rantai atasan dari `$requesterId` sampai paling atas (Root).

**Laravel Eloquent Strategy (Looping/Recursive Relationship):**
Gunakan relasi `uppline` pada model `Employment`.

```php
$uplines = [];
$currentEmployee = Employment::find($requesterId);

while ($currentEmployee && $currentEmployee->uppline_id) {
    $parent = Employment::find($currentEmployee->uppline_id);
    if (!$parent) break;

    $uplines[] = [
        'employment_id' => $parent->id,
        'job_level_id'  => $parent->job_level_id, // Pastikan ini integer/cocok dengan config
        'name'          => $parent->employment_name // atau field name yang sesuai
    ];

    $currentEmployee = $parent;
}

```

### Step 4: The Intersection (Match & Filter)

Lakukan filter daftar `$uplines` (Step 3) menggunakan aturan `$allowedJobLevels` (Step 2).

**Algorithm:**

1. Loop melalui `$allowedJobLevels` (agar urutan approval sesuai settingan sequence, bukan urutan hierarki).
2. Untuk setiap level yang dibutuhkan, cari di array `$uplines` apakah ada atasan dengan `job_level_id` yang sesuai.
3. Jika **MATCH**: Masukkan ke array final `$approvers`.
4. Jika **NO MATCH**: Skip (artinya atasan level itu required di config, tapi user tidak punya atasan di level itu - *Optional: Throw error jika mandatory*).

---

## 4. Code Snippet for AI Agent (Laravel Context)

Berikut adalah *blueprint* fungsi yang diharapkan dibuat di Service/Action Class:

```php
/**
 * Generate Dynamic Upline Approvers
 *
 * @param int $templateId
 * @param int $requesterEmploymentId
 * @return array List of formatted approvers for details table
 */
public function resolveUplineApprovers($templateId, $requesterEmploymentId)
{
    // 1. Get Requester Division
    $requester = Employment::findOrFail($requesterEmploymentId);
    // ASSUMPTION: Logic to get division_id exists. Adjust 'division_id' column name as per schema.
    $divisionId = $requester->division_id ?? null; 

    // 2. Get Config (Using the Priority Query)
    $requiredLevels = DB::table('approval_flow_uppline_configs')
        ->select('job_level_id', 'step_sequence')
        ->where('template_id', $templateId)
        ->where(function($query) use ($templateId, $divisionId) {
            $query->where('division_id', $divisionId)
                  ->orWhere(function($q) use ($templateId, $divisionId) {
                      $q->whereNull('division_id')
                        ->whereNotExists(function($sub) use ($templateId, $divisionId) {
                            $sub->select(DB::raw(1))
                                ->from('approval_flow_uppline_configs as sub_c')
                                ->where('sub_c.template_id', $templateId)
                                ->where('sub_c.division_id', $divisionId);
                        });
                  });
        })
        ->orderBy('step_sequence', 'asc')
        ->get();

    // If no config found, return empty (no upline required)
    if ($requiredLevels->isEmpty()) {
        return [];
    }

    // 3. Get All Uplines (Recursive)
    $uplinesMap = [];
    $iterator = $requester;
    
    // Safety break to prevent infinite loops
    $maxDepth = 20; 
    $depth = 0;

    while ($iterator->uppline_id && $depth < $maxDepth) {
        $upline = Employment::find($iterator->uppline_id);
        if (!$upline) break;
        
        // Map job_level_id to the upline object for easy lookup
        // Assuming job_level_id in Employment matches job_level_id in Config
        $uplinesMap[$upline->job_level_id] = $upline;
        
        $iterator = $upline;
        $depth++;
    }

    // 4. Intersect & Build Final Result
    $finalApprovers = [];
    
    foreach ($requiredLevels as $config) {
        if (isset($uplinesMap[$config->job_level_id])) {
            $approver = $uplinesMap[$config->job_level_id];
            
            $finalApprovers[] = [
                'phase'           => 'uppline',
                'level_sequence'  => $config->step_sequence,
                'employment_id'   => $approver->id,
                'employment_name' => $approver->organization_name, // Adjust column
                'status'          => 'pending',
                // Add other necessary fields for 'approval_request_details'
            ];
        }
    }

    return $finalApprovers;
}

```

## 5. Testing Scenarios (Checklist)

Pastikan kode yang dihasilkan lulus tes logika ini:

1. **Scenario IT (Deep Chain):**
* Set Config IT (`div_id=1`) butuh Level 3, 4, 5, 6.
* Requester orang IT.
* *Expectation:* Approver list harus berisi Section, Dept, Div, Director.


2. **Scenario Finance (Short Chain):**
* Set Config Finance (`div_id=2`) butuh Level 3, 4, 5.
* Requester orang Finance.
* *Expectation:* Approver list harus berisi Section, Dept, Div (Director **TIDAK** masuk meskipun user punya atasan Director).


3. **Scenario General (Default):**
* Requester orang HR (tidak punya config spesifik).
* *Expectation:* Menggunakan config yang `division_id = NULL`.