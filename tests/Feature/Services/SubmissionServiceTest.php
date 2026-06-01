<?php

namespace Tests\Feature\Services;

use App\Models\CompanyPolicy;
use App\Models\CompanyPolicyDetail;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\JobLevel;
use App\Models\JobPosition;
use App\Models\KPIDepartment;
use App\Models\KPIDivision;
use App\Models\KPIWorkPlan;
use App\Services\SubmissionService\SubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubmissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private SubmissionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SubmissionService::class);
    }

    private function createEmployeeWithRole(string $roleName, string $email): Employee
    {
        $employee = Employee::create([
            'email' => $email,
            'password' => bcrypt('password'),
            'first_name' => 'Test',
            'last_name' => 'User',
            'status' => 'active',
        ]);

        Role::findOrCreate($roleName, 'web');
        $employee->assignRole($roleName);

        return $employee;
    }

    private function attachDivisionEmployment(Employee $employee, Division $division): void
    {
        $jobPosition = JobPosition::create([
            'job_position_name' => 'Head Division',
            'job_level_id' => 2,
            'job_level_name' => 'Division',
            'structure_id' => $division->id,
            'structure_name' => $division->id,
            'status' => 'active',
        ]);

        Employment::create([
            'employee_id' => $employee->id,
            'job_level_id' => '2',
            'job_level_name' => 'Division',
            'job_position_id' => (string) $jobPosition->id,
            'job_position_name' => $jobPosition->job_position_name,
            'status' => 'active',
        ]);
    }

    private function createDepartmentWorkplan(Division $division, string $activity, int $year): KPIWorkPlan
    {
        $department = Department::create([
            'division_id' => $division->id,
            'name' => 'Dept ' . $activity,
            'status' => 'active',
        ]);

        $policy = CompanyPolicy::create([
            'tahun' => $year,
            'nama_dokumen' => 'Policy ' . $activity,
            'file_path' => 'company-policy/' . $activity . '.pdf',
        ]);

        $policyDetail = CompanyPolicyDetail::create([
            'company_policy_id' => $policy->id,
            'strategic_goal' => 'Goal ' . $activity,
            'description' => 'Desc',
            'target' => 'Target',
        ]);

        $kpiDivision = KPIDivision::create([
            'company_policy_detail_id' => $policyDetail->id,
            'division_id' => $division->id,
            'year' => $year,
            'division_goals' => 'Division Goals ' . $activity,
        ]);

        $kpiDepartment = KPIDepartment::create([
            'kpi_division_id' => $kpiDivision->id,
            'department_id' => $department->id,
            'year' => $year,
            'department_goals' => 'Department Goals ' . $activity,
        ]);

        return KPIWorkPlan::create([
            'kpi_type' => 'department',
            'kpi_id' => $kpiDepartment->id,
            'year' => $year,
            'activity' => $activity,
            'status' => 'approved',
        ]);
    }

    public function test_get_user_page_data_admin_sees_all_workplans(): void
    {
        $year = (int) now()->format('Y');
        $divA = Division::create(['name' => 'Division A', 'status' => 'active']);
        $divB = Division::create(['name' => 'Division B', 'status' => 'active']);

        $wpA = $this->createDepartmentWorkplan($divA, 'Program A', $year);
        $wpB = $this->createDepartmentWorkplan($divB, 'Program B', $year);

        $admin = $this->createEmployeeWithRole('Super Admin', 'admin-submission@example.test');
        $this->actingAs($admin, 'web');

        $data = $this->service->getUserPageData();
        $workplanIds = $data['workplans']->pluck('id')->all();

        $this->assertCount(2, $workplanIds);
        $this->assertContains($wpA->id, $workplanIds);
        $this->assertContains($wpB->id, $workplanIds);
    }

    public function test_get_user_page_data_non_admin_only_sees_login_division_workplans(): void
    {
        $year = (int) now()->format('Y');
        $divA = Division::create(['name' => 'Division A', 'status' => 'active']);
        $divB = Division::create(['name' => 'Division B', 'status' => 'active']);

        $wpA = $this->createDepartmentWorkplan($divA, 'Program A', $year);
        $this->createDepartmentWorkplan($divB, 'Program B', $year);

        $user = $this->createEmployeeWithRole('User', 'user-submission@example.test');
        $this->attachDivisionEmployment($user, $divA);
        $this->actingAs($user, 'web');

        $data = $this->service->getUserPageData();
        $workplanIds = $data['workplans']->pluck('id')->all();

        $this->assertCount(1, $workplanIds);
        $this->assertEquals($wpA->id, $workplanIds[0]);
    }

    public function test_get_programs_by_job_level_admin_sees_all_division_programs(): void
    {
        $year = (int) now()->format('Y');
        $divA = Division::create(['name' => 'Division A', 'status' => 'active']);
        $divB = Division::create(['name' => 'Division B', 'status' => 'active']);
        $this->createDepartmentWorkplan($divA, 'Program A', $year);
        $this->createDepartmentWorkplan($divB, 'Program B', $year);

        $jobLevel = JobLevel::create([
            'job_level_name' => 'Manager',
            'status' => 'active',
        ]);

        $admin = $this->createEmployeeWithRole('Super Admin', 'admin-programs@example.test');
        $this->actingAs($admin, 'web');

        $result = $this->service->getProgramsByJobLevel($jobLevel->id);

        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['data']);
    }

    public function test_get_programs_by_job_level_non_admin_only_sees_login_division_programs(): void
    {
        $year = (int) now()->format('Y');
        $divA = Division::create(['name' => 'Division A', 'status' => 'active']);
        $divB = Division::create(['name' => 'Division B', 'status' => 'active']);
        $wpA = $this->createDepartmentWorkplan($divA, 'Program A', $year);
        $this->createDepartmentWorkplan($divB, 'Program B', $year);

        $jobLevel = JobLevel::create([
            'job_level_name' => 'Manager',
            'status' => 'active',
        ]);

        $user = $this->createEmployeeWithRole('User', 'user-programs@example.test');
        $this->attachDivisionEmployment($user, $divA);
        $this->actingAs($user, 'web');

        $result = $this->service->getProgramsByJobLevel($jobLevel->id);

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['data']);
        $this->assertEquals($wpA->id, $result['data'][0]['id']);
    }
}

