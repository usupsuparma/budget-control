<?php

namespace Tests\Feature;

use App\Models\CompanyPolicy;
use App\Models\CompanyPolicyDetail;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\KPIDepartment;
use App\Models\KPIDivision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class KPIDepartmentFeatureTest extends TestCase
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

    public function test_authorized_user_can_open_kpi_department_page(): void
    {
        $this->actingAsAdminEmployee();
        $year = now()->year;
        $division = $this->createDivision('Finance Division');
        $department = $this->createDepartment($division, 'Finance Department');
        $detail = $this->createCompanyPolicyDetail($year, 'Finance policy');
        $kpiDivision = $this->createKpiDivision($detail, $division, $year);

        $response = $this->get(route('KPIDepartment.index'));

        $response
            ->assertOk()
            ->assertViewIs('pages.kpi.department_rev1')
            ->assertViewHas('title', 'KPI Department')
            ->assertViewHas('currentYear', $year)
            ->assertSee('KPI Department')
            ->assertSee('Add New KPI Department')
            ->assertSee('kpi-department-config', false);

        $this->assertTrue($response->viewData('kpiDivisions')->contains('id', $kpiDivision->id));
        $this->assertTrue($response->viewData('departments')->contains('id', $department->id));
        $this->assertArrayHasKey('store', $response->viewData('kpiDepartmentUrls'));
        $this->assertArrayHasKey('departmentsByDivision', $response->viewData('kpiDepartmentUrls'));
    }

    public function test_authorized_user_can_load_kpi_division_and_department_dropdowns(): void
    {
        $this->actingAsAdminEmployee();
        $year = now()->year;
        $division = $this->createDivision('Operations Division');
        $departmentA = $this->createDepartment($division, 'Production Department');
        $departmentB = $this->createDepartment($division, 'Maintenance Department');
        $detail = $this->createCompanyPolicyDetail($year, 'Operations policy');
        $kpiDivision = $this->createKpiDivision($detail, $division, $year, [
            'division_goals' => 'Improve operations reliability',
        ]);

        $this
            ->withHeader('Accept', 'application/json')
            ->get(route('KPIDepartment.kpiDivisions', ['year' => $year]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $kpiDivision->id)
            ->assertJsonPath('data.0.text', '[' . $year . '] Improve operations reliability');

        $this
            ->withHeader('Accept', 'application/json')
            ->get(route('KPIDepartment.departmentsByDivision', [
                'kpi_division_id' => $kpiDivision->id,
            ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'id' => $departmentA->id,
                'text' => 'Production Department',
            ])
            ->assertJsonFragment([
                'id' => $departmentB->id,
                'text' => 'Maintenance Department',
            ]);
    }

    public function test_authorized_user_can_create_kpi_department_from_page_payload(): void
    {
        $this->actingAsAdminEmployee();
        $year = now()->year;
        $division = $this->createDivision('Commercial Division');
        $department = $this->createDepartment($division, 'Sales Department');
        $detail = $this->createCompanyPolicyDetail($year, 'Commercial policy');
        $kpiDivision = $this->createKpiDivision($detail, $division, $year);
        $token = 'kpi-department-create-token';

        $response = $this
            ->withHeader('Accept', 'application/json')
            ->withSession(['_token' => $token])
            ->post(route('KPIDepartment.store'), $this->departmentPayload($kpiDivision, $department, $year, [
                '_token' => $token,
            ]));

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Department row created successfully.',
            ])
            ->assertJsonPath('data.id', fn ($id) => is_int($id));

        $this->assertDatabaseHas('kpi_department', [
            'kpi_division_id' => $kpiDivision->id,
            'department_id' => $department->id,
            'year' => $year,
            'department_goals' => 'Department Goal ' . $year,
            'department_activities' => 'Department Activities ' . $year,
            'target_department' => '90%',
            'duration_days' => 30,
            'schedule_start' => $year . '-01-01',
            'schedule_end' => $year . '-01-31',
            'jan' => 1,
            'feb' => 0,
            'mar' => 0,
            'revenue_cost' => 'Revenue',
            'pic' => 'Andi',
            'description' => 'KPI department integration test',
        ]);
    }

    public function test_authorized_user_can_get_datatable_rows_filtered_by_year(): void
    {
        $this->actingAsAdminEmployee();
        $currentYear = now()->year;
        $previousYear = $currentYear - 1;
        $division = $this->createDivision('Supply Chain Division');
        $department = $this->createDepartment($division, 'Logistics Department');
        $currentDetail = $this->createCompanyPolicyDetail($currentYear, 'Current year policy');
        $previousDetail = $this->createCompanyPolicyDetail($previousYear, 'Previous year policy');
        $currentKpiDivision = $this->createKpiDivision($currentDetail, $division, $currentYear);
        $previousKpiDivision = $this->createKpiDivision($previousDetail, $division, $previousYear, [
            'division_goals' => 'Previous division goals',
        ]);

        $this->createKpiDepartment($currentKpiDivision, $department, $currentYear);
        $previousKpiDepartment = $this->createKpiDepartment($previousKpiDivision, $department, $previousYear, [
            'department_goals' => 'Previous department goals',
            'department_activities' => 'Previous activities',
            'schedule_start' => $previousYear . '-03-01',
            'schedule_end' => $previousYear . '-03-31',
            'mar' => true,
        ]);

        $response = $this
            ->withHeader('Accept', 'application/json')
            ->get(route('KPIDepartment.datatable', ['year' => $previousYear]));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'success',
                'message' => 'Data berhasil diambil.',
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $previousKpiDepartment->id)
            ->assertJsonPath('data.0.year', $previousYear)
            ->assertJsonPath('data.0.kpi_division', 'Previous division goals')
            ->assertJsonPath('data.0.department', 'Logistics Department')
            ->assertJsonPath('data.0.department_goals', 'Previous department goals')
            ->assertJsonPath('data.0.mar', true);
    }

    public function test_authorized_user_can_show_and_update_kpi_department(): void
    {
        $this->actingAsAdminEmployee();
        $year = now()->year;
        $division = $this->createDivision('IT Division');
        $department = $this->createDepartment($division, 'Infrastructure Department');
        $detail = $this->createCompanyPolicyDetail($year, 'IT policy');
        $kpiDivision = $this->createKpiDivision($detail, $division, $year);
        $kpiDepartment = $this->createKpiDepartment($kpiDivision, $department, $year);
        $token = 'kpi-department-update-token';

        $this
            ->withHeader('Accept', 'application/json')
            ->get(route('KPIDepartment.show', $kpiDepartment->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $kpiDepartment->id)
            ->assertJsonPath('data.kpi_division_id', $kpiDivision->id)
            ->assertJsonPath('data.department_id', $department->id);

        $response = $this
            ->withHeader('Accept', 'application/json')
            ->withSession(['_token' => $token])
            ->post(route('KPIDepartment.update', $kpiDepartment->id), $this->departmentPayload($kpiDivision, $department, $year, [
                '_token' => $token,
                '_method' => 'PUT',
                'department_goals' => 'Updated infrastructure goals',
                'department_activities' => 'Updated infrastructure activities',
                'target_department' => '99%',
                'duration_days' => 45,
                'schedule_start' => $year . '-04-01',
                'schedule_end' => $year . '-05-15',
                'jan' => 0,
                'feb' => 0,
                'apr' => 1,
                'may' => 1,
                'revenue_cost' => 'Cost',
                'pic' => 'Budi',
                'description' => 'Updated KPI department description',
            ]));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Department row updated successfully.',
                'data' => ['id' => $kpiDepartment->id],
            ]);

        $this->assertDatabaseHas('kpi_department', [
            'id' => $kpiDepartment->id,
            'department_goals' => 'Updated infrastructure goals',
            'department_activities' => 'Updated infrastructure activities',
            'target_department' => '99%',
            'duration_days' => 45,
            'schedule_start' => $year . '-04-01',
            'schedule_end' => $year . '-05-15',
            'jan' => 0,
            'feb' => 0,
            'apr' => 1,
            'may' => 1,
            'revenue_cost' => 'Cost',
            'pic' => 'Budi',
            'description' => 'Updated KPI department description',
        ]);
    }

    public function test_authorized_user_can_delete_kpi_department(): void
    {
        $this->actingAsAdminEmployee();
        $year = now()->year;
        $division = $this->createDivision('HR Division');
        $department = $this->createDepartment($division, 'People Development Department');
        $detail = $this->createCompanyPolicyDetail($year, 'People policy');
        $kpiDivision = $this->createKpiDivision($detail, $division, $year);
        $kpiDepartment = $this->createKpiDepartment($kpiDivision, $department, $year);
        $token = 'kpi-department-delete-token';

        $this
            ->withHeader('Accept', 'application/json')
            ->withSession(['_token' => $token])
            ->post(route('KPIDepartment.destroy', $kpiDepartment->id), [
                '_token' => $token,
                '_method' => 'DELETE',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Department berhasil dihapus.',
                'data' => ['id' => $kpiDepartment->id],
            ]);

        $this->assertSoftDeleted('kpi_department', [
            'id' => $kpiDepartment->id,
        ]);
    }

    private function actingAsAdminEmployee(): Employee
    {
        $employee = Employee::create([
            'email' => 'kpi-department-tester@example.test',
            'password' => bcrypt('password'),
            'first_name' => 'KPI',
            'last_name' => 'Department Tester',
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
            'description' => 'KPI department integration test',
        ], $overrides));
    }

    private function departmentPayload(
        KPIDivision $kpiDivision,
        Department $department,
        int $year,
        array $overrides = []
    ): array {
        return array_merge([
            'year' => $year,
            'kpi_division_id' => $kpiDivision->id,
            'department_id' => $department->id,
            'department_goals' => 'Department Goal ' . $year,
            'department_activities' => 'Department Activities ' . $year,
            'target_department' => '90%',
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
            'pic' => 'Andi',
            'description' => 'KPI department integration test',
        ], $overrides);
    }
}
