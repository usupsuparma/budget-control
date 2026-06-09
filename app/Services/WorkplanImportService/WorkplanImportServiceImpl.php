<?php

namespace App\Services\WorkplanImportService;

use App\Exceptions\DomainException;
use App\Models\BudgetCategory;
use App\Models\BudgetCode;
use App\Models\Department;
use App\Models\Division;
use App\Models\CompanyPolicyDetail;
use App\Models\KPIDepartment;
use App\Models\KPIDivision;
use App\Models\KPISection;
use App\Models\KPIWorkPlan;
use App\Models\Section;
use App\Models\WorkplanBudgetItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkplanImportServiceImpl implements WorkplanImportService
{
    public function importWorkplanBudget(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip header lines (Assuming 2 header rows based on standard CSV template)
        $header1 = fgetcsv($handle, 0, ';');
        $header2 = fgetcsv($handle, 0, ';');

        if (!$header1 || !$header2) {
            fclose($handle);
            throw new DomainException("Format CSV tidak valid atau file kosong.");
        }

        $processed = 0;
        $skipped = 0;
        $year = (int) date('Y'); // Using current year as default
        
        $rowNumber = 2; // We skip two header rows (indexed conceptually)

        try {
            DB::transaction(function () use ($handle, &$processed, &$skipped, $year, &$rowNumber) {
                while (($row = fgetcsv($handle, 0, ';')) !== false) {
                    $rowNumber++;
                    try {
                        if (empty($row[0]) || !isset($row[2])) {
                            continue; // Skip empty rows
                        }
                        
                        // Minimum valid row length requirement based on CSV pattern (Code + Price offsets)
                        if (count($row) < 32) {
                            $skipped++;
                            continue;
                        }

                        $code = $row[0];
                        $activeFlag = $row[2];
                        $inchargeCode = $row[3];
                        $remarks = $row[4] ?? '';
                        
                        // Format decimal to standard float
                        $priceStr = str_replace(',', '.', str_replace('.', '', $row[7]));
                        $price = floatval($priceStr);
                        
                        // Rules: Flag must be 1, Price >= 1
                        if ($activeFlag != 1 || $price < 1) {
                            $skipped++;
                            continue;
                        }

                        // Verify Budget Code
                        $budgetCodeModel = BudgetCode::where('budget_code', $code)->first();
                        $budgetCodeValue = $budgetCodeModel ? $budgetCodeModel->budget_code : $code; 

                        // 1. Resolve Organization structure
                        $orgInfo = $this->resolveOrganization($inchargeCode);
                        if (!$orgInfo) {
                            $skipped++;
                            continue; // Skip if organization is not found
                        }

                        // 2. Resolve/Create KPI Hierarchy
                        $kpiWorkplanId = $this->resolveKpiWorkplan($orgInfo, $year);

                        // 3. Resolve Budget Category from REMARKS
                        $budgetCategory = BudgetCategory::where('name', $remarks)->first();
                        $budgetCategoryId = $budgetCategory ? $budgetCategory->id : 1;

                        // 4. Create Budget Item Data
                        $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
                        $activityData = [];
                        $baseMonthIdx = 8; // jan Qty Index
                        
                        foreach ($months as $idx => $month) {
                            $qtyIdx = $baseMonthIdx + ($idx * 2);
                            $rawQty = $row[$qtyIdx] ?? '0';
                            // Clean format just like price, in case it has dots or commas
                            $cleanQty = str_replace(',', '.', str_replace('.', '', $rawQty));
                            $activityData["activity_{$month}"] = (float) $cleanQty;
                        }

                        WorkplanBudgetItem::create(array_merge([
                            'kpi_workplan_id'    => $kpiWorkplanId,
                            'budget_code'        => $budgetCodeValue,
                            'budget_category_id' => $budgetCategoryId,
                            'price_estimation'   => $price,
                            'price_final'        => $price,
                            'stock_code'         => $row[5] ?? null,
                            'cost_center'        => $inchargeCode,
                            'description'        => $row[1] ?? '',
                            'notes'              => $remarks,
                            'status'             => 'draft', // Ready for transaction flows
                            'sort_order'         => 0,
                            'category_type'      => 'Routine'
                        ], $activityData));

                        $processed++;
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Error on CSV Row {$rowNumber}: " . implode(';', $row));
                        throw new DomainException("Error di baris ke-{$rowNumber}: " . $e->getMessage());
                    }
                }
            });
        } catch (\Exception $e) {
            fclose($handle);
            Log::error('Import transaction failed: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }

        fclose($handle);

        return [
            'processed' => $processed,
            'skipped'   => $skipped
        ];
    }

    private function resolveOrganization(string $code): ?array
    {
        $section = Section::where('code', $code)->first();
        if ($section) {
            return ['type' => 'section', 'id' => $section->id, 'model' => $section];
        }

        $department = Department::where('code', $code)->first();
        if ($department) {
            return ['type' => 'department', 'id' => $department->id, 'model' => $department];
        }

        $division = Division::where('code', $code)->first();
        if ($division) {
            return ['type' => 'division', 'id' => $division->id, 'model' => $division];
        }

        return null;
    }

    private function resolveKpiWorkplan(array $orgInfo, int $year): int
    {
        if ($orgInfo['type'] === 'section') {
            $section = $orgInfo['model'];
            $kpiSection = $this->getOrCreateKpiSection($section, $year);
            
            $kpiWorkplan = KPIWorkPlan::firstOrCreate([
                'kpi_type' => 'section',
                'kpi_id'   => $kpiSection->id,
                'year'     => $year,
            ], [
                'activity' => 'Generated Activity from CSV for Section ' . $section->name,
                'status'   => 'draft'
            ]);
            
            return $kpiWorkplan->id;
        }

        if ($orgInfo['type'] === 'department') {
            $department = $orgInfo['model'];
            $KPIDepartment = $this->getOrCreateKpiDepartment($department, $year);

            $kpiWorkplan = KPIWorkPlan::firstOrCreate([
                'kpi_type' => 'department',
                'kpi_id'   => $KPIDepartment->id,
                'year'     => $year,
            ], [
                'activity' => 'Generated Activity from CSV for Department ' . $department->name,
                'status'   => 'draft'
            ]);
            
            return $kpiWorkplan->id;
        }

        if ($orgInfo['type'] === 'division') {
            // Since KPIWorkPlan morphs to 'department' or 'section' mostly,
            // we map a 'division' code to its first accessible department or create a generic requirement.
            $division = $orgInfo['model'];
            $this->getOrCreateKpiDivision($division, $year);
            
            $department = Department::where('division_id', $division->id)->first();
            if (!$department) {
                throw new DomainException("Tidak dapat membuat KPI untuk Divisi " . $division->code . " karena entitas tidak memiliki Departemen.");
            }
            
            // Re-resolve via the virtual mapping to the matched child department
            return $this->resolveKpiWorkplan(['type' => 'department', 'id' => $department->id, 'model' => $department], $year);
        }

        throw new DomainException("Tipe Organisasi tidak diketahui.");
    }

    private function getOrCreateKpiDivision(Division $division, int $year): KPIDivision
    {
        $policyDetailId = 3; // Menggunakan ID 3 sesuai arahan user

        return KPIDivision::firstOrCreate([
            'division_id' => $division->id,
            'year'        => $year,
        ], [
            'company_policy_detail_id' => $policyDetailId,
            'division_goals'  => 'Generated Division Goals from Import',
            'target_division' => 'Generated Target from Import',
            'duration_days'   => 365,
            'schedule_start'  => $year.'-01-01',
            'schedule_end'    => $year.'-12-31',
        ]);
    }

    private function getOrCreateKpiDepartment(Department $department, int $year): KPIDepartment
    {
        $division = $department->division;
        $kpiDivision = null;
        if ($division) {
            $kpiDivision = $this->getOrCreateKpiDivision($division, $year);
        }

        return KPIDepartment::firstOrCreate([
            'department_id' => $department->id,
            'year'          => $year,
        ], [
            'kpi_division_id'       => $kpiDivision ? $kpiDivision->id : null,
            'department_goals'      => 'Generated Dept Goals from Import',
            'department_activities' => 'Generated Dept Activities from Import',
            'target_department'     => 'Generated Dept Target from Import',
            'duration_days'         => 365,
            'schedule_start'        => $year.'-01-01',
            'schedule_end'          => $year.'-12-31',
        ]);
    }

    private function getOrCreateKpiSection(Section $section, int $year): KPISection
    {
        $department = $section->department;
        $KPIDepartment = null;
        if ($department) {
            $KPIDepartment = $this->getOrCreateKpiDepartment($department, $year);
        }

        return KPISection::firstOrCreate([
            'section_id' => $section->id,
            'year'       => $year,
        ], [
            'kpi_department_id' => $KPIDepartment ? $KPIDepartment->id : null,
            'section_goals'     => 'Generated Section Goals from Import',
            'activities'        => 'Generated Section Activities from Import',
            'target_section'    => 'Generated Target from Import',
            'duration_days'     => 365,
            'schedule_start'    => $year.'-01-01',
            'schedule_end'      => $year.'-12-31',
        ]);
    }
}
