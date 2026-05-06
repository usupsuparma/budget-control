<?php

namespace Tests\Feature\Services;

use App\DTOs\KPISectionData;
use App\Models\CompanyPolicy;
use App\Models\CompanyPolicyDetail;
use App\Models\Department;
use App\Models\Division;
use App\Models\KPIDivision;
use App\Models\KPIDepartement;
use App\Models\KPISection;
use App\Models\Section;
use App\Services\KPISectionService\KPISectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KPISectionServiceTest extends TestCase
{
    use RefreshDatabase;

    private KPISectionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(KPISectionService::class);
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

    private function createSection(Department $department): Section
    {
        return Section::create([
            'department_id' => $department->id,
            'name' => 'Section A',
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

    private function createKpiDepartment(KPIDivision $kpiDivision, Department $department, int $year): KPIDepartement
    {
        return KPIDepartement::create([
            'kpi_division_id' => $kpiDivision->id,
            'department_id' => $department->id,
            'year' => $year,
            'department_goals' => 'Department Goals ' . $year,
        ]);
    }

    private function makeData(KPIDepartement $KPIDepartement, Section $section, int $year, array $overrides = []): KPISectionData
    {
        $payload = array_merge([
            'year' => $year,
            'kpi_department_id' => $KPIDepartement->id,
            'section_id' => $section->id,
            'section_goals' => 'Section Goals ' . $year,
            'activities' => 'Activities ' . $year,
            'target_section' => '90%',
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
            'unit_id' => 'UNIT-1',
            'description' => 'Description',
        ], $overrides);

        return KPISectionData::fromArray($payload);
    }

    public function test_get_index_data_includes_current_year_and_existing_years(): void
    {
        $currentYear = now()->year;
        $previousYear = $currentYear - 1;

        $policy = $this->createCompanyPolicy($previousYear);
        $detail = $this->createCompanyPolicyDetail($policy);
        $division = $this->createDivision();
        $department = $this->createDepartment($division);
        $section = $this->createSection($department);
        $kpiDivision = $this->createKpiDivision($detail, $division, $previousYear);
        $KPIDepartement = $this->createKpiDepartment($kpiDivision, $department, $previousYear);

        KPISection::create([
            'kpi_department_id' => $KPIDepartement->id,
            'section_id' => $section->id,
            'year' => $previousYear,
            'section_goals' => 'Section Goals ' . $previousYear,
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
        $section = $this->createSection($department);
        $kpiDivision = $this->createKpiDivision($detail, $division, $currentYear);
        $KPIDepartement = $this->createKpiDepartment($kpiDivision, $department, $currentYear);

        $this->service->create($this->makeData($KPIDepartement, $section, $currentYear));
        $this->service->create($this->makeData($KPIDepartement, $section, $previousYear, [
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

    public function test_create_and_find_kpi_section(): void
    {
        $year = now()->year;
        $policy = $this->createCompanyPolicy($year);
        $detail = $this->createCompanyPolicyDetail($policy);
        $division = $this->createDivision();
        $department = $this->createDepartment($division);
        $section = $this->createSection($department);
        $kpiDivision = $this->createKpiDivision($detail, $division, $year);
        $KPIDepartement = $this->createKpiDepartment($kpiDivision, $department, $year);

        $data = $this->makeData($KPIDepartement, $section, $year);
        $kpi = $this->service->create($data);

        $this->assertDatabaseHas('kpi_section', [
            'id' => $kpi->id,
            'year' => $year,
            'section_id' => $section->id,
        ]);

        $found = $this->service->find($kpi->id);
        $this->assertEquals($kpi->id, $found->id);
    }

    public function test_update_kpi_section(): void
    {
        $year = now()->year;
        $policy = $this->createCompanyPolicy($year);
        $detail = $this->createCompanyPolicyDetail($policy);
        $division = $this->createDivision();
        $department = $this->createDepartment($division);
        $section = $this->createSection($department);
        $kpiDivision = $this->createKpiDivision($detail, $division, $year);
        $KPIDepartement = $this->createKpiDepartment($kpiDivision, $department, $year);

        $kpi = $this->service->create($this->makeData($KPIDepartement, $section, $year));

        $updatedData = $this->makeData($KPIDepartement, $section, $year, [
            'section_goals' => 'Updated Goals',
            'target_section' => '99%',
        ]);

        $updated = $this->service->update($kpi->id, $updatedData);

        $this->assertEquals('Updated Goals', $updated->section_goals);
        $this->assertDatabaseHas('kpi_section', [
            'id' => $kpi->id,
            'section_goals' => 'Updated Goals',
            'target_section' => '99%',
        ]);
    }

    public function test_delete_kpi_section(): void
    {
        $year = now()->year;
        $policy = $this->createCompanyPolicy($year);
        $detail = $this->createCompanyPolicyDetail($policy);
        $division = $this->createDivision();
        $department = $this->createDepartment($division);
        $section = $this->createSection($department);
        $kpiDivision = $this->createKpiDivision($detail, $division, $year);
        $KPIDepartement = $this->createKpiDepartment($kpiDivision, $department, $year);

        $kpi = $this->service->create($this->makeData($KPIDepartement, $section, $year));

        $this->service->delete($kpi->id);

        $this->assertDatabaseMissing('kpi_section', [
            'id' => $kpi->id,
        ]);
    }
}
