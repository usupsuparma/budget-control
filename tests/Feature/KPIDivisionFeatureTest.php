<?php

namespace Tests\Feature;

use App\Models\CompanyPolicy;
use App\Models\CompanyPolicyDetail;
use App\Models\Division;
use App\Models\Employee;
use App\Models\KPIDivision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class KPIDivisionFeatureTest extends TestCase
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

    public function test_authorized_user_can_open_kpi_division_page(): void
    {
        $this->actingAsAdminEmployee();
        $year = now()->year;
        $division = $this->createDivision('Finance Division');
        $detail = $this->createCompanyPolicyDetail($year, 'Improve cash flow');

        $response = $this->get(route('kpidivision.index'));

        $response
            ->assertOk()
            ->assertViewIs('pages.kpi.division_rev1')
            ->assertViewHas('title', 'KPI Division')
            ->assertViewHas('currentYear', $year)
            ->assertSee('KPI Division')
            ->assertSee('Add New KPI Division')
            ->assertSee('kpi-division-config', false);

        $this->assertTrue($response->viewData('divisions')->contains('id', $division->id));
        $this->assertTrue($response->viewData('companyPolicies')->contains('id', $detail->id));
        $this->assertArrayHasKey('store', $response->viewData('kpiDivisionUrls'));
    }

    public function test_authorized_user_can_create_kpi_division_from_page_payload(): void
    {
        $this->actingAsAdminEmployee();
        $year = now()->year;
        $division = $this->createDivision('Operations Division');
        $detail = $this->createCompanyPolicyDetail($year, 'Improve production reliability');
        $token = 'kpi-division-create-token';

        $response = $this
            ->withHeader('Accept', 'application/json')
            ->withSession(['_token' => $token])
            ->post(route('kpidivision.store'), $this->kpiPayload($detail, $division, $year, [
                '_token' => $token,
            ]));

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Division row created successfully.',
            ])
            ->assertJsonPath('data.id', fn ($id) => is_int($id));

        $this->assertDatabaseHas('kpi_division', [
            'company_policy_detail_id' => $detail->id,
            'division_id' => $division->id,
            'year' => $year,
            'division_goals' => 'Division Goal ' . $year,
            'target_division' => '95%',
            'duration_days' => 31,
            'schedule_start' => $year . '-01-01',
            'schedule_end' => $year . '-02-01',
            'jan' => 1,
            'feb' => 1,
            'mar' => 0,
            'revenue_cost' => 'Revenue',
            'pic' => 'Andi',
            'description' => 'KPI division integration test',
        ]);
    }

    public function test_authorized_user_can_get_datatable_rows_filtered_by_year(): void
    {
        $this->actingAsAdminEmployee();
        $currentYear = now()->year;
        $previousYear = $currentYear - 1;
        $division = $this->createDivision('Commercial Division');
        $currentDetail = $this->createCompanyPolicyDetail($currentYear, 'Current year policy');
        $previousDetail = $this->createCompanyPolicyDetail($previousYear, 'Previous year policy');

        $this->createKpiDivision($currentDetail, $division, $currentYear);
        $previousKpi = $this->createKpiDivision($previousDetail, $division, $previousYear, [
            'division_goals' => 'Previous year goals',
            'schedule_start' => $previousYear . '-03-01',
            'schedule_end' => $previousYear . '-03-31',
            'mar' => true,
        ]);

        $response = $this
            ->withHeader('Accept', 'application/json')
            ->get(route('kpidivision.datatable', ['year' => $previousYear]));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'success',
                'message' => 'Data berhasil diambil.',
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $previousKpi->id)
            ->assertJsonPath('data.0.year', $previousYear)
            ->assertJsonPath('data.0.company_policy', 'Previous year policy')
            ->assertJsonPath('data.0.division', 'Commercial Division')
            ->assertJsonPath('data.0.mar', true);
    }

    public function test_authorized_user_can_show_and_update_kpi_division(): void
    {
        $this->actingAsAdminEmployee();
        $year = now()->year;
        $division = $this->createDivision('Supply Chain Division');
        $detail = $this->createCompanyPolicyDetail($year, 'Supply chain policy');
        $kpi = $this->createKpiDivision($detail, $division, $year);
        $token = 'kpi-division-update-token';

        $this
            ->withHeader('Accept', 'application/json')
            ->get(route('kpidivision.show', $kpi->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $kpi->id)
            ->assertJsonPath('data.division_id', $division->id);

        $response = $this
            ->withHeader('Accept', 'application/json')
            ->withSession(['_token' => $token])
            ->post(route('kpidivision.update', $kpi->id), $this->kpiPayload($detail, $division, $year, [
                '_token' => $token,
                '_method' => 'PUT',
                'division_goals' => 'Updated supply chain goals',
                'target_division' => '99%',
                'duration_days' => 45,
                'schedule_start' => $year . '-04-01',
                'schedule_end' => $year . '-05-15',
                'jan' => 0,
                'feb' => 0,
                'apr' => 1,
                'may' => 1,
                'revenue_cost' => 'Cost',
                'pic' => 'Budi',
                'description' => 'Updated KPI division description',
            ]));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Division row updated successfully.',
                'data' => ['id' => $kpi->id],
            ]);

        $this->assertDatabaseHas('kpi_division', [
            'id' => $kpi->id,
            'division_goals' => 'Updated supply chain goals',
            'target_division' => '99%',
            'duration_days' => 45,
            'schedule_start' => $year . '-04-01',
            'schedule_end' => $year . '-05-15',
            'jan' => 0,
            'feb' => 0,
            'apr' => 1,
            'may' => 1,
            'revenue_cost' => 'Cost',
            'pic' => 'Budi',
            'description' => 'Updated KPI division description',
        ]);
    }

    public function test_authorized_user_can_inline_update_and_delete_kpi_division(): void
    {
        $this->actingAsAdminEmployee();
        $year = now()->year;
        $division = $this->createDivision('HR Division');
        $detail = $this->createCompanyPolicyDetail($year, 'People policy');
        $kpi = $this->createKpiDivision($detail, $division, $year, [
            'jan' => false,
        ]);
        $inlineToken = 'kpi-division-inline-token';
        $deleteToken = 'kpi-division-delete-token';

        $this
            ->withHeader('Accept', 'application/json')
            ->withSession(['_token' => $inlineToken])
            ->patch(route('kpidivision.inline', $kpi->id), [
                '_token' => $inlineToken,
                'field' => 'jan',
                'value' => '1',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.field', 'jan')
            ->assertJsonPath('data.value', true)
            ->assertJsonPath('data.display_value', 'Yes');

        $this->assertDatabaseHas('kpi_division', [
            'id' => $kpi->id,
            'jan' => 1,
        ]);

        $this
            ->withHeader('Accept', 'application/json')
            ->withSession(['_token' => $deleteToken])
            ->delete(route('kpidivision.destroy', $kpi->id), [
                '_token' => $deleteToken,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'status' => 'success',
                'message' => 'KPI Division berhasil dihapus.',
                'data' => ['id' => $kpi->id],
            ]);

        $this->assertDatabaseMissing('kpi_division', [
            'id' => $kpi->id,
        ]);
    }

    private function actingAsAdminEmployee(): Employee
    {
        $employee = Employee::create([
            'email' => 'kpi-division-tester@example.test',
            'password' => bcrypt('password'),
            'first_name' => 'KPI',
            'last_name' => 'Division Tester',
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
            'description' => 'KPI division integration test',
        ], $overrides));
    }

    private function kpiPayload(
        CompanyPolicyDetail $detail,
        Division $division,
        int $year,
        array $overrides = []
    ): array {
        return array_merge([
            'year' => $year,
            'company_policy_detail_id' => $detail->id,
            'division_id' => $division->id,
            'division_goals' => 'Division Goal ' . $year,
            'target_division' => '95%',
            'duration_days' => 31,
            'schedule_start' => $year . '-01-01',
            'schedule_end' => $year . '-02-01',
            'jan' => 1,
            'feb' => 1,
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
            'description' => 'KPI division integration test',
        ], $overrides);
    }
}
