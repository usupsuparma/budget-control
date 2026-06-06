# Perbaikan Approval Chain - WorkplanBudgetItem

## Masalah yang Ditemukan

Ketika submit approval untuk item yang memiliki `use_uppline_chain = true`, sistem hanya memasukkan approver dari uppline chain saja ke dalam `ApprovalRequestDetail`. Approver dari `ApprovalFlowDetail` (master flow) tidak ikut masuk.

### Expected Behavior
Jika `use_uppline_chain = true`, maka approval chain harus berisi:
1. **Phase 1 (Uppline)**: Approver dari `ApprovalFlowUpplineConfigs`
2. **Phase 2 (Master Flow)**: Approver dari `ApprovalFlowDetail`

Dan `level_sequence` harus berurutan dari 1, 2, 3, dst... untuk kedua phase.

## Solusi yang Diterapkan

### 1. Perbaikan Method `buildApprovalChain()`

**File**: `app/Services/WorkplanBudgetItemApprovalService.php`

**Perubahan**:
- Menambahkan variable `$levelSequence` untuk tracking urutan level secara sequential
- Setiap approver yang ditambahkan ke chain akan mendapat `level_sequence` yang berurutan
- Menambahkan log yang lebih detail untuk debugging

```php
protected function buildApprovalChain(...): array {
    $chain = [];
    $levelSequence = 1; // Start from 1

    // Phase 1: Uppline Chain
    if ($template->use_uppline_chain) {
        $upplineApprovers = $this->resolveUplineApprovers(...);
        foreach ($upplineApprovers as $approver) {
            $chain[] = array_merge($approver, [
                'phase' => 'uppline',
                'level_sequence' => $levelSequence++, // Sequential increment
            ]);
        }
    }

    // Phase 2: Master Flow
    $masterFlowApprovers = $this->getMasterFlowApprovers(...);
    foreach ($masterFlowApprovers as $approver) {
        $chain[] = array_merge($approver, [
            'phase' => 'master_flow',
            'level_sequence' => $levelSequence++, // Continue sequence
        ]);
    }

    return $chain;
}
```

### 2. Perbaikan Method `submitForApproval()`

**Perubahan**:
- Menggunakan `level_sequence` yang sudah dikalkulasi dari chain
- Menghapus kalkulasi `$index + 1` yang bisa menyebabkan duplikasi level

**Sebelum**:
```php
foreach ($approvalChain as $index => $approver) {
    ApprovalRequestDetail::create([
        'level_sequence' => $index + 1, // Bermasalah
        ...
    ]);
}
```

**Sesudah**:
```php
foreach ($approvalChain as $approver) {
    ApprovalRequestDetail::create([
        'level_sequence' => $approver['level_sequence'], // From chain
        ...
    ]);
}
```

### 3. Enhanced Logging

Menambahkan log yang lebih detail di beberapa method:
- `buildApprovalChain()`: Log untuk Phase 1 dan Phase 2
- `getMasterFlowApprovers()`: Log untuk query result dan mapping
- `submitForApproval()`: Log untuk final chain sebelum submission

## Contoh Flow Setelah Perbaikan

### Scenario: Template dengan uppline chain + threshold

**Template Config**:
- `use_uppline_chain = true`
- `use_threshold = true`
- `condition_field = 'total'`

**ApprovalFlowUpplineConfigs**:
1. Step 1: Supervisor (job_level_name = 'Supervisor')
2. Step 2: Manager (job_level_name = 'Manager')

**ApprovalFlowDetail**:
1. Level 1: Employment ID 10 (threshold: 10,000,000)
2. Level 2: Employment ID 11 (threshold: 50,000,000)
3. Level 3: Employment ID 12 (threshold: 100,000,000)

**Submit item dengan total = 45,000,000**

### Result Chain:

| Level | Phase | Employment | Reason |
|-------|-------|------------|--------|
| 1 | uppline | Supervisor (ID 5) | From uppline chain |
| 2 | uppline | Manager (ID 6) | From uppline chain |
| 3 | master_flow | Employment 10 | 45M > 10M (threshold passed) |
| 4 | master_flow | Employment 11 | 45M < 50M (threshold met) |

**Catatan**: Employment 12 tidak masuk karena threshold 100M > 45M (tidak relevan).

## Testing

### Cara Testing Manual:

1. **Setup template dengan uppline chain**:
   ```sql
   UPDATE approval_flow_templates 
   SET use_uppline_chain = 1, use_threshold = 1 
   WHERE id = 1;
   ```

2. **Setup uppline configs**:
   ```sql
   INSERT INTO approval_flow_uppline_configs 
   (template_id, division_id, step_sequence, job_level_name) 
   VALUES 
   (1, NULL, 1, 'Supervisor'),
   (1, NULL, 2, 'Manager');
   ```

3. **Setup master flow details**:
   ```sql
   INSERT INTO approval_flow_details 
   (template_id, level_sequence, employment_id, threshold_amount, is_required) 
   VALUES 
   (1, 1, 10, 10000000, 1),
   (1, 2, 11, 50000000, 1),
   (1, 3, 12, 100000000, 1);
   ```

4. **Submit item for approval**:
   - Buat atau edit workplan budget item
   - Klik "Submit for Approval"
   - Check log file: `storage/logs/laravel.log`

5. **Verify hasil di database**:
   ```sql
   SELECT 
       ard.level_sequence,
       ard.phase,
       ard.employment_name,
       ard.status
   FROM approval_request_details ard
   WHERE request_id = [REQUEST_ID]
   ORDER BY level_sequence;
   ```

### Expected Result:
Harus melihat semua approver (uppline + master flow) dalam urutan level_sequence yang benar.

## Log Output Example

```
[INFO] Phase 1: Uppline Chain
  count: 2
  approvers: [
    {employment_id: 5, employment_name: "John (Supervisor)", ...},
    {employment_id: 6, employment_name: "Jane (Manager)", ...}
  ]

[INFO] Phase 2: Master Flow
  count: 2
  approvers: [
    {employment_id: 10, employment_name: "Director A", threshold: 10000000},
    {employment_id: 11, employment_name: "Director B", threshold: 50000000}
  ]

[INFO] Final approval chain built
  total_levels: 4
  chain: [
    {level_sequence: 1, phase: "uppline", employment_id: 5, ...},
    {level_sequence: 2, phase: "uppline", employment_id: 6, ...},
    {level_sequence: 3, phase: "master_flow", employment_id: 10, ...},
    {level_sequence: 4, phase: "master_flow", employment_id: 11, ...}
  ]
```

## Troubleshooting

### Issue: Master flow tidak muncul

**Cek**:
1. Pastikan `approval_flow_details` memiliki data untuk template_id tersebut
2. Jika `use_threshold = true`, cek threshold_amount vs item.total
3. Check log untuk melihat query result dari `getMasterFlowApprovers()`

### Issue: Level sequence tidak berurutan

**Cek**:
1. Pastikan tidak ada kode lain yang mengubah level_sequence setelah buildApprovalChain()
2. Verify bahwa method submitForApproval menggunakan `$approver['level_sequence']`

### Issue: Uppline chain kosong

**Cek**:
1. Pastikan `approval_flow_uppline_configs` memiliki data
2. Cek bahwa job_level_name di config sesuai dengan job_level_name di employment
3. Pastikan uppline_id di employment table sudah terisi dengan benar

## Related Files

- `app/Services/WorkplanBudgetItemApprovalService.php` - Main service
- `app/Models/ApprovalFlowUpplineConfigs.php` - Uppline config model
- `app/Models/ApprovalFlowDetail.php` - Master flow detail model
- `app/Models/ApprovalRequestDetail.php` - Result detail model
- `documentasi/APPROVAL_SYSTEM.md` - Complete approval system docs

## Changelog

**Date**: 2026-01-16

**Changes**:
1. Fixed buildApprovalChain() to properly sequence both uppline and master flow approvers
2. Updated submitForApproval() to use pre-calculated level_sequence from chain
3. Enhanced getMasterFlowApprovers() with better logging and data mapping
4. Added comprehensive logs for debugging approval chain building

**Impact**: 
- ✅ Both uppline and master flow approvers now properly included in approval chain
- ✅ Level sequence is now sequential across all phases
- ✅ Better debugging capability with enhanced logs
