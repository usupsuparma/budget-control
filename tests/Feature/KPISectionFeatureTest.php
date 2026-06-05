<?php

namespace Tests\Feature;

use App\Models\CompanyPolicy;
use App\Models\CompanyPolicyDetail;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\KPIDepartment;
use App\Models\KPIDivision;
use App\Models\KPISection;
use App\Models\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class KPISectionFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('mysql', config('database.default'));
        $this->assertSame('budget_control_testing', config('database.connections.mysql.database'));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        parent::tearDown();
    }

    public function test_authorized_user_can_open_kpi_section_page(): void
    {
        $this->actingAsAdminEmployee();
        $year = now()->year;
        $division = $this->createDivision('Finance Division');
        $department = $this->createDepartment($division, 'Finance Department');
        $section = $this->createSection($department, 'Budget Control Section');
        $detail = $this->createCompanyPolicyDetail($year, 'Finance section policy');
        $kpiDivision = $this->createKpiDivision($detail, $division, $year);
        $kpiDepartment = $this->createKpiDepartment($kpiDivision, $department, $year);

        $response = $this->get(route('kpisection.index'));

        $response
            ->assertOk()
            ->assertViewIs('pages.kpi.section_rev1')
            ->assertViewHas('title', 'KPI Section')
            ->assertViewHas('currentYear', $year)
            ->assertSee('KPI Section')
            ->assertSee('Add KPI Section')
            ->assertSee('kpi-section-config', false);

        $this->assertTrue($response->viewData('kpiDepartments')->contains('id', $kpiDepartment->id));
        $this->assertTrue($response->viewData('sections')->contains('id', $section->id));
        $this->assertArrayHasKey('store', $response->viewData('kpiSectionUrls'));
        $this->assertArrayHasKey('sections', $response->viewData('kpiSectionUrls'));
    }

    public function test_authorized_user_can_load_kpi_department_and_section_dropdowns(): void
    {
        $this->actingAsAdminEmployee();
        $year = now()->year;
        $division = $this->createDivision('Operations Division');
        $department = $this->createDepartment($division, 'Production Department');
        $sectionA = $this->createSection($department, 'Maintenance Section');
        $sectionB = $this->createSection($department, 'Process Section');
        $detail = $this->createCompanyPolicyDetail($year, 'Operations section policy');
        $kpiDivision = $this->createKpiDivision($detail, $division, $year);
        $kpiDepartment = $this->createKpiDepartment($kpiDivision, $department, $year, [
            'department_goals' => 'Improve production reliability',
        ]);

        $this
            ->withHeader('Accept', 'application/json')
            ->get(route('kpisection.kpiDepartments', ['year' => $year]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $kpiDepartment->id)
            ->assertJsonPath('data.0.text', '[' . $year . '] Improve production reliability');

        $this
            ->withHeader('Accept', 'application/json')
            ->get(route('kpisection.sections', [
                'kpi_department_id' => $kpiDepartment->id,
            ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'id' => $sectionA->id,
                'text' => 'Maintenance Section',
            ])
            ->assertJsonFragment([
                'id' => $sectionB->id,
                'text' => 'Process Section',
            ]);
    }

    public function test_authorized_user_can_create_kpi_section_from_page_payload(): void
    {
        $this->actingAsAdminEmployee();
        $year = now()->year;
        $division = $this->createDivision('Commercial Division');
        $department = $this->createDepartment($division, 'Sales Department');
        $section = $this->createSection($department, 'Domestic Sales Section');
        $detail = $this->createCompanyPolicyDetail($year, 'Commercial section policy');
        $kpiDivision = $this->createKpiDivision($detail, $division, $year);
        $kpiDepartment = $this->createKpiDepartment($kpiDivision, $department, $year);
        $token = 'kpi-section-create-token';

        $response = $this
            ->withHeader('Accept', 'application/json')
            ->withSession(['_token' => $token])
            ->post(route('kpisection.store'), $this->sectionPayload($kpiDepartment, $section, $year, [
                '_token' => $token,
            ]));

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Section created successfully.',
            ])
            ->assertJsonPath('data.id', fn ($id) => is_int($id));

        $this->assertDatabaseHas('kpi_section', [
            'kpi_department_id' => $kpiDepartment->id,
            'section_id' => $section->id,
            'year' => $year,
            'section_goals' => 'Section Goal ' . $year,
            'activities' => 'Section Activities ' . $year,
            'target_section' => '90%',
            'duration_days' => 30,
            'schedule_start' => $year . '-01-01',
            'schedule_end' => $year . '-01-31',
            'jan' => 1,
            'feb' => 0,
            'mar' => 0,
            'revenue_cost' => 'Revenue',
            'unit_id' => 'UNIT-SECTION',
            'description' => 'KPI section integration test',
        ]);
    }

    public function test_authorized_user_can_get_datatable_rows_filtered_by_year(): void
    {
        $this->actingAsAdminEmployee();
        $currentYear = now()->year;
        $previousYear = $currentYear - 1;
        $division = $this->createDivision('Supply Chain Division');
        $department = $this->createDepartment($division, 'Logistics Department');
        $section = $this->createSection($department, 'Warehouse Section');
        $currentDetail = $this->createCompanyPolicyDetail($currentYear, 'Current year policy');
        $previousDetail = $this->createCompanyPolicyDetail($previousYear, 'Previous year policy');
        $currentKpiDivision = $this->createKpiDivision($currentDetail, $division, $currentYear);
        $previousKpiDivision = $this->createKpiDivision($previousDetail, $division, $previousYear);
        $currentKpiDepartment = $this->createKpiDepartment($currentKpiDivision, $department, $currentYear);
        $previousKpiDepartment = $this->createKpiDepartment($previousKpiDivision, $department, $previousYear, [
            'department_goals' => 'Previous department goals',
        ]);

        $this->createKpiSection($currentKpiDepartment, $section, $currentYear);
        $previousKpiSection = $this->createKpiSection($previousKpiDepartment, $section, $previousYear, [
            'section_goals' => 'Previous section goals',
            'activities' => 'Previous activities',
            'schedule_start' => $previousYear . '-03-01',
            'schedule_end' => $previousYear . '-03-31',
            'mar' => true,
        ]);

        $response = $this
            ->withHeader('Accept', 'application/json')
            ->get(route('kpisection.datatable', ['year' => $previousYear]));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'success',
                'message' => 'Data berhasil diambil.',
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $previousKpiSection->id)
            ->assertJsonPath('data.0.year', $previousYear)
            ->assertJsonPath('data.0.kpi_department', 'Previous department goals')
            ->assertJsonPath('data.0.section', 'Warehouse Section')
            ->assertJsonPath('data.0.section_goals', 'Previous section goals')
            ->assertJsonPath('data.0.mar.value', 1)
            ->assertJsonPath('data.0.mar.label', 'Yes');
    }

    public function test_authorized_user_can_show_and_update_kpi_section(): void
    {
        $this->actingAsAdminEmployee();
        $year = now()->year;
        $division = $this->createDivision('IT Division');
        $department = $this->createDepartment($division, 'Infrastructure Department');
        $section = $this->createSection($department, 'Network Section');
        $detail = $this->createCompanyPolicyDetail($year, 'IT section policy');
        $kpiDivision = $this->createKpiDivision($detail, $division, $year);
        $kpiDepartment = $this->createKpiDepartment($kpiDivision, $department, $year);
        $kpiSection = $this->createKpiSection($kpiDepartment, $section, $year);
        $token = 'kpi-section-update-token';

        $this
            ->withHeader('Accept', 'application/json')
            ->get(route('kpisection.show', $kpiSection->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $kpiSection->id)
            ->assertJsonPath('data.kpi_department_id', $kpiDepartment->id)
            ->assertJsonPath('data.section_id', $section->id);

        $response = $this
            ->withHeader('Accept', 'application/json')
            ->withSession(['_token' => $token])
            ->post(route('kpisection.update', $kpiSection->id), $this->sectionPayload($kpiDepartment, $section, $year, [
                '_token' => $token,
                '_method' => 'PUT',
                'section_goals' => 'Updated network goals',
                'activities' => 'Updated network activities',
                'target_section' => '99%',
                'duration_days' => 45,
                'schedule_start' => $year . '-04-01',
                'schedule_end' => $year . '-05-15',
                'jan' => 0,
                'feb' => 0,
                'apr' => 1,
                'may' => 1,
                'revenue_cost' => 'Cost',
                'unit_id' => 'UNIT-NETWORK',
                'description' => 'Updated KPI section description',
            ]));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Section updated successfully.',
                'data' => ['id' => $kpiSection->id],
            ]);

        $this->assertDatabaseHas('kpi_section', [
            'id' => $kpiSection->id,
            'section_goals' => 'Updated network goals',
            'activities' => 'Updated network activities',
            'target_section' => '99%',
            'duration_days' => 45,
            'schedule_start' => $year . '-04-01',
            'schedule_end' => $year . '-05-15',
            'jan' => 0,
            'feb' => 0,
            'apr' => 1,
            'may' => 1,
            'revenue_cost' => 'Cost',
            'unit_id' => 'UNIT-NETWORK',
            'description' => 'Updated KPI section description',
        ]);
    }

    public function test_authorized_user_can_delete_kpi_section(): void
    {
        $this->actingAsAdminEmployee();
        $year = now()->year;
        $division = $this->createDivision('HR Division');
        $department = $this->createDepartment($division, 'People Development Department');
        $section = $this->createSection($department, 'Training Section');
        $detail = $this->createCompanyPolicyDetail($year, 'People section policy');
        $kpiDivision = $this->createKpiDivision($detail, $division, $year);
        $kpiDepartment = $this->createKpiDepartment($kpiDivision, $department, $year);
        $kpiSection = $this->createKpiSection($kpiDepartment, $section, $year);
        $token = 'kpi-section-delete-token';

        $this
            ->withHeader('Accept', 'application/json')
            ->withSession(['_token' => $token])
            ->delete(route('kpisection.destroy', $kpiSection->id), [
                '_token' => $token,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Section deleted successfully.',
                'data' => ['id' => $kpiSection->id],
            ]);

        $this->assertSoftDeleted('kpi_section', [
            'id' => $kpiSection->id,
        ]);
    }

    private function actingAsAdminEmployee(): Employee
    {
        $employee = Employee::create([
            'email' => 'kpi-section-tester@example.test',
            'password' => bcrypt('password'),
            'first_name' => 'KPI',
            'last_name' => 'Section Tester',
            'status' => 'Active',
        ]);

        Role::findOrCreate('Super Admin', 'web');
        $employee->assignRole('Super Admin');

        $this->actingAs($employee, 'web');

        return $employee;
    }

    private function createCompanyPolicyDetail(int $year, string $goal): CompanyPolicyDetail
    {
        $policy = CompanyPolicy::create([
            'tahun' => $year,
            'nama_dokumen' => 'Company Policy FY' . $year,
            'file_path' => 'company-policy/' . $year . '.pdf',
        ]);

        return CompanyPolicyDetail::create([
            'company_policy_id' => $policy->id,
            'strategic_goal' => $goal,
            'description' => 'Policy detail description ' . $year,
            'strategic_goal_id' => 'Kebijakan ' . $year,
            'description_id' => 'Deskripsi kebijakan ' . $year,
            'target' => '0',
        ]);
    }

    private function createDivision(string $name): Division
    {
        return Division::create([
            'name' => $name,
            'status' => 'active',
        ]);
    }

    private function createDepartment(Division $division, string $name): Department
    {
        return Department::create([
            'division_id' => $division->id,
            'name' => $name,
            'status' => 'active',
        ]);
    }

    private function createSection(Department $department, string $name): Section
    {
        return Section::create([
            'department_id' => $department->id,
            'name' => $name,
            'status' => 'active',
        ]);
    }

    private function createKpiDivision(
        CompanyPolicyDetail $detail,
        Division $division,
        int $year,
        array $overrides = []
    ): KPIDivision {
        return KPIDivision::create(array_merge([
            'company_policy_detail_id' => $detail->id,
            'division_id' => $division->id,
            'year' => $year,
            'division_goals' => 'Division Goal ' . $year,
            'target_division' => '95%',
            'duration_days' => 31,
            'schedule_start' => $year . '-01-01',
            'schedule_end' => $year . '-02-01',
            'jan' => true,
            'feb' => true,
            'revenue_cost' => 'Revenue',
            'pic' => 'Andi',
            'description' => 'KPI division integration test',
        ], $overrides));
    }

    private function createKpiDepartment(
        KPIDivision $kpiDivision,
        Department $department,
        int $year,
        array $overrides = []
    ): KPIDepartment {
        return KPIDepartment::create(array_merge([
            'kpi_division_id' => $kpiDivision->id,
            'department_id' => $department->id,
            'year' => $year,
            'department_goals' => 'Department Goal ' . $year,
            'department_activities' => 'Department Activities ' . $year,
            'target_department' => '90%',
            'duration_days' => 30,
            'schedule_start' => $year . '-01-01',
            'schedule_end' => $year . '-01-31',
            'jan' => true,
            'feb' => false,
            'revenue_cost' => 'Revenue',
            'pic' => 'Andi',
            'description' => 'KPI department integration test',
        ], $overrides));
    }

    private function createKpiSection(
        KPIDepartment $kpiDepartment,
        Section $section,
        int $year,
        array $overrides = []
    ): KPISection {
        return KPISection::create(array_merge([
            'kpi_department_id' => $kpiDepartment->id,
            'section_id' => $section->id,
            'year' => $year,
            'section_goals' => 'Section Goal ' . $year,
            'activities' => 'Section Activities ' . $year,
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
            'unit_id' => 'UNIT-SECTION',
            'description' => 'KPI section integration test',
        ], $overrides));
    }

    private function sectionPayload(
        KPIDepartment $kpiDepartment,
        Section $section,
        int $year,
        array $overrides = []
    ): array {
        return array_merge([
            'year' => $year,
            'kpi_department_id' => $kpiDepartment->id,
            'section_id' => $section->id,
            'section_goals' => 'Section Goal ' . $year,
            'activities' => 'Section Activities ' . $year,
            'target_section' => '90%',
            'duration_days' => 30,
            'schedule_start' => $year . '-01-01',
            'schedule_end' => $year . '-01-31',
            'jan' => 1,
            'feb' => 0,
            'mar' => 0,
            'apr' => 0,
            'may' => 0,
            'jun' => 0,
            'jul' => 0,
            'aug' => 0,
            'sep' => 0,
            'oct' => 0,
            'nov' => 0,
            'dec' => 0,
            'revenue_cost' => 'Revenue',
            'unit_id' => 'UNIT-SECTION',
            'description' => 'KPI section integration test',
        ], $overrides);
    }
}
