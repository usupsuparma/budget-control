<?php

namespace Tests\Feature\Services;

use App\DTOs\KPIDepartmentData;
use App\Models\CompanyPolicy;
use App\Models\CompanyPolicyDetail;
use App\Models\Department;
use App\Models\Division;
use App\Models\KPIDivision;
use App\Models\KPIDepartement;
use App\Services\KPIDepartmentService\KPIDepartmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KPIDepartmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private KPIDepartmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(KPIDepartmentService::class);
    }

    private function createCompanyPolicy(int $year): CompanyPolicy
    {
        return CompanyPolicy::create([
            'tahun' => $year,
            'nama_dokumen' => 'Doc ' . $year,
            'file_path' => 'company-policy/' . $year . '.pdf',
        ]);
    }

    private function createCompanyPolicyDetail(CompanyPolicy $policy): CompanyPolicyDetail
    {
        return CompanyPolicyDetail::create([
            'company_policy_id' => $policy->id,
            'strategic_goal' => 'Goal ' . $policy->tahun,
            'description' => 'Desc',
            'target' => 'Target',
        ]);
    }

    private function createDivision(): Division
    {
        return Division::create([
            'name' => 'Division A',
            'status' => 'active',
        ]);
    }

    private function createDepartment(Division $division): Department
    {
        return Department::create([
            'division_id' => $division->id,
            'name' => 'Department A',
            'status' => 'active',
        ]);
    }

    private function createKpiDivision(CompanyPolicyDetail $detail, Division $division, int $year): KPIDivision
    {
        return KPIDivision::create([
            'company_policy_detail_id' => $detail->id,
            'division_id' => $division->id,
            'year' => $year,
            'division_goals' => 'Division Goals ' . $year,
        ]);
    }

    private function makeData(KPIDivision $kpiDivision, Department $department, int $year, array $overrides = []): KPIDepartmentData
    {
        $payload = array_merge([
            'year' => $year,
            'kpi_division_id' => $kpiDivision->id,
            'department_id' => $department->id,
            'department_goals' => 'Dept Goals ' . $year,
            'department_activities' => 'Activities ' . $year,
            'target_department' => '90%',
            'duration_days' => 30,
            'schedule_start' => $year . '-01-01',
            'schedule_end' => $year . '-01-31',
            'jan' => true,
            'feb' => false,
            'mar' => false,
            'apr' => false,
            'may' => false,
            'jun' => false,
            'jul' => false,
            'aug' => false,
            'sep' => false,
            'oct' => false,
            'nov' => false,
            'dec' => false,
            'revenue_cost' => 'Revenue',
            'pic' => 'Andi',
            'description' => 'Description',
        ], $overrides);

        return KPIDepartmentData::fromArray($payload);
    }

    public function test_get_index_data_includes_current_year_and_existing_years(): void
    {
        $currentYear = now()->year;
        $previousYear = $currentYear - 1;

        $policy = $this->createCompanyPolicy($previousYear);
        $detail = $this->createCompanyPolicyDetail($policy);
        $division = $this->createDivision();
        $department = $this->createDepartment($division);
        $kpiDivision = $this->createKpiDivision($detail, $division, $previousYear);

        KPIDepartement::create([
            'kpi_division_id' => $kpiDivision->id,
            'department_id' => $department->id,
            'year' => $previousYear,
            'department_goals' => 'Dept Goals ' . $previousYear,
        ]);

        $data = $this->service->getIndexData();

        $this->assertEquals($currentYear, $data['currentYear']);
        $this->assertContains($currentYear, $data['kpiYears']);
        $this->assertContains($previousYear, $data['kpiYears']);
        $this->assertEquals([$currentYear, $previousYear], $data['kpiYears']);
    }

    public function test_get_data_table_rows_filters_by_year(): void
    {
        $currentYear = now()->year;
        $previousYear = $currentYear - 1;

        $policy = $this->createCompanyPolicy($currentYear);
        $detail = $this->createCompanyPolicyDetail($policy);
        $division = $this->createDivision();
        $department = $this->createDepartment($division);
        $kpiDivision = $this->createKpiDivision($detail, $division, $currentYear);

        $this->service->create($this->makeData($kpiDivision, $department, $currentYear));
        $this->service->create($this->makeData($kpiDivision, $department, $previousYear, [
            'schedule_start' => $previousYear . '-02-01',
            'schedule_end' => $previousYear . '-02-28',
        ]));

        $rowsPrevious = $this->service->getDataTableRows($previousYear);
        $this->assertCount(1, $rowsPrevious);
        $this->assertEquals($previousYear, $rowsPrevious[0]['year']);

        $rowsCurrent = $this->service->getDataTableRows(null);
        $this->assertCount(1, $rowsCurrent);
        $this->assertEquals($currentYear, $rowsCurrent[0]['year']);
    }

    public function test_create_and_find_kpi_department(): void
    {
        $year = now()->year;
        $policy = $this->createCompanyPolicy($year);
        $detail = $this->createCompanyPolicyDetail($policy);
        $division = $this->createDivision();
        $department = $this->createDepartment($division);
        $kpiDivision = $this->createKpiDivision($detail, $division, $year);

        $data = $this->makeData($kpiDivision, $department, $year);
        $kpiDept = $this->service->create($data);

        $this->assertDatabaseHas('kpi_department', [
            'id' => $kpiDept->id,
            'year' => $year,
            'department_id' => $department->id,
        ]);

        $found = $this->service->find($kpiDept->id);
        $this->assertEquals($kpiDept->id, $found->id);
    }

    public function test_update_kpi_department(): void
    {
        $year = now()->year;
        $policy = $this->createCompanyPolicy($year);
        $detail = $this->createCompanyPolicyDetail($policy);
        $division = $this->createDivision();
        $department = $this->createDepartment($division);
        $kpiDivision = $this->createKpiDivision($detail, $division, $year);

        $kpiDept = $this->service->create($this->makeData($kpiDivision, $department, $year));

        $updatedData = $this->makeData($kpiDivision, $department, $year, [
            'department_goals' => 'Updated Goals',
            'target_department' => '99%',
        ]);

        $updated = $this->service->update($kpiDept->id, $updatedData);

        $this->assertEquals('Updated Goals', $updated->department_goals);
        $this->assertDatabaseHas('kpi_department', [
            'id' => $kpiDept->id,
            'department_goals' => 'Updated Goals',
            'target_department' => '99%',
        ]);
    }

    public function test_delete_kpi_department(): void
    {
        $year = now()->year;
        $policy = $this->createCompanyPolicy($year);
        $detail = $this->createCompanyPolicyDetail($policy);
        $division = $this->createDivision();
        $department = $this->createDepartment($division);
        $kpiDivision = $this->createKpiDivision($detail, $division, $year);

        $kpiDept = $this->service->create($this->makeData($kpiDivision, $department, $year));

        $this->service->delete($kpiDept->id);

        $this->assertDatabaseMissing('kpi_department', [
            'id' => $kpiDept->id,
        ]);
    }
}
