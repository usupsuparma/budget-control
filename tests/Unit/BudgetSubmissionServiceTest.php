<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Division;
use App\Models\KPIWorkPlan;
use App\Models\BudgetSubmission;
use App\Services\BudgetSubmissionService\BudgetSubmissionServiceImpl;
use App\Services\BudgetSubmissionService\DTOs\BudgetSubmissionData;
use App\Services\UserRoleService\UserRoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;
use Illuminate\Database\Eloquent\Collection;

class BudgetSubmissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private $userRoleServiceMock;
    private $budgetSubmissionService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userRoleServiceMock = Mockery::mock(UserRoleService::class);
        $this->budgetSubmissionService = new BudgetSubmissionServiceImpl($this->userRoleServiceMock);
    }

    public function test_get_index_data_for_admin_returns_all_divisions()
    {
        $admin = User::factory()->create();
        Division::factory()->count(3)->create();

        $this->userRoleServiceMock->shouldReceive('isAdmin')
            ->once()
            ->with($admin)
            ->andReturn(true);

        $this->userRoleServiceMock->shouldReceive('getDivisionIds')
            ->never();

        $data = $this->budgetSubmissionService->getIndexData($admin);

        $this->assertTrue($data['isAdmin']);
        $this->assertEquals(3, $data['divisions']->count());
    }

    public function test_get_index_data_for_non_admin_returns_filtered_divisions()
    {
        $user = User::factory()->create();
        $allowedDivisions = Division::factory()->count(2)->create();
        $otherDivision = Division::factory()->create();

        $this->userRoleServiceMock->shouldReceive('isAdmin')
            ->once()
            ->with($user)
            ->andReturn(false);

        $this->userRoleServiceMock->shouldReceive('getDivisionIds')
            ->once()
            ->with($user)
            ->andReturn($allowedDivisions->pluck('id')->toArray());

        $data = $this->budgetSubmissionService->getIndexData($user);

        $this->assertFalse($data['isAdmin']);
        $this->assertEquals(2, $data['divisions']->count());
        $this->assertFalse($data['divisions']->contains('id', $otherDivision->id));
    }

    public function test_store_creates_submission()
    {
        $user = User::factory()->create();
        $division = Division::factory()->create(['name' => 'IT Division']);
        $workPlan = KPIWorkPlan::factory()->create();

        $dto = BudgetSubmissionData::fromArray([
            'division_id' => $division->id,
            'submission_date' => '2026-06-02',
            'type' => 'add',
            'work_plan_id' => $workPlan->id,
            'budget_account_id' => 10,
            'estimation_amount' => 5000000,
            'description' => 'Test submission',
        ]);

        $this->budgetSubmissionService->store($dto, $user);

        $this->assertDatabaseHas('budget_submissions', [
            'user_id' => $user->id,
            'division_id' => $division->id,
            'division_name' => 'IT Division',
            'estimation_amount' => 5000000,
            'status' => 0,
        ]);
    }
}
