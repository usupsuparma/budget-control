<?php

namespace Tests\Feature\Services;

use App\DTOs\KPIDivisionData;
use App\Models\CompanyPolicy;
use App\Models\CompanyPolicyDetail;
use App\Models\Division;
use App\Models\KPIDivision;
use App\Services\KPIDivisionService\KPIDivisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KPIDivisionServiceTest extends TestCase
{
    use RefreshDatabase;

    private KPIDivisionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(KPIDivisionService::class);
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

    private function makeData(CompanyPolicyDetail $detail, Division $division, int $year, array $overrides = []): KPIDivisionData
    {
        $payload = array_merge([
            'year' => $year,
            'company_policy_detail_id' => $detail->id,
            'division_id' => $division->id,
            'division_goals' => 'Goal ' . $year,
            'target_division' => '95%',
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

        return KPIDivisionData::fromArray($payload);
    }

    public function test_get_index_data_includes_current_year_and_existing_years(): void
    {
        $currentYear = now()->year;
        $previousYear = $currentYear - 1;

        $policy = $this->createCompanyPolicy($previousYear);
        $detail = $this->createCompanyPolicyDetail($policy);
        $division = $this->createDivision();

        KPIDivision::create([
            'company_policy_detail_id' => $detail->id,
            'division_id' => $division->id,
            'year' => $previousYear,
            'division_goals' => 'Goal ' . $previousYear,
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

        $this->service->create($this->makeData($detail, $division, $currentYear));
        $this->service->create($this->makeData($detail, $division, $previousYear, [
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

    public function test_create_and_find_kpi_division(): void
    {
        $year = now()->year;
        $policy = $this->createCompanyPolicy($year);
        $detail = $this->createCompanyPolicyDetail($policy);
        $division = $this->createDivision();

        $data = $this->makeData($detail, $division, $year);
        $kpi = $this->service->create($data);

        $this->assertDatabaseHas('kpi_division', [
            'id' => $kpi->id,
            'year' => $year,
            'division_id' => $division->id,
        ]);

        $found = $this->service->find($kpi->id);
        $this->assertEquals($kpi->id, $found->id);
    }

    public function test_update_kpi_division(): void
    {
        $year = now()->year;
        $policy = $this->createCompanyPolicy($year);
        $detail = $this->createCompanyPolicyDetail($policy);
        $division = $this->createDivision();

        $kpi = $this->service->create($this->makeData($detail, $division, $year));

        $updatedData = $this->makeData($detail, $division, $year, [
            'division_goals' => 'Updated Goals',
            'target_division' => '99%',
        ]);

        $updated = $this->service->update($kpi->id, $updatedData);

        $this->assertEquals('Updated Goals', $updated->division_goals);
        $this->assertDatabaseHas('kpi_division', [
            'id' => $kpi->id,
            'division_goals' => 'Updated Goals',
            'target_division' => '99%',
        ]);
    }

    public function test_delete_kpi_division(): void
    {
        $year = now()->year;
        $policy = $this->createCompanyPolicy($year);
        $detail = $this->createCompanyPolicyDetail($policy);
        $division = $this->createDivision();

        $kpi = $this->service->create($this->makeData($detail, $division, $year));

        $this->service->delete($kpi->id);

        $this->assertDatabaseMissing('kpi_division', [
            'id' => $kpi->id,
        ]);
    }

    public function test_inline_update_kpi_division(): void
    {
        $year = now()->year;
        $policy = $this->createCompanyPolicy($year);
        $detail = $this->createCompanyPolicyDetail($policy);
        $division = $this->createDivision();

        $kpi = $this->service->create($this->makeData($detail, $division, $year));

        $result = $this->service->inlineUpdate($kpi->id, 'jan', '1');

        $this->assertEquals('Yes', $result['display_value']);
        $this->assertDatabaseHas('kpi_division', [
            'id' => $kpi->id,
            'jan' => 1,
        ]);
    }
}
