<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Division;
use App\Models\KPIWorkPlan;
use App\Models\BudgetSubmission;
use App\Models\BudgetCategory;
use App\Models\BudgetMutation;
use App\Models\WorkplanBudgetItem;
use App\Exceptions\DomainException;
use App\Services\BudgetLedgerService\BudgetLedgerServiceImpl;
use App\Services\BudgetSubmissionApprovalService\BudgetSubmissionApprovalServiceImpl;
use App\Services\BudgetSubmissionService\BudgetSubmissionServiceImpl;
use App\Services\BudgetSubmissionService\DTOs\BudgetSubmissionData;
use App\Services\LogService\LogService;
use App\Services\UserRoleService\UserRoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Mockery;

class BudgetSubmissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private $userRoleServiceMock;
    private $budgetSubmissionService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userRoleServiceMock = Mockery::mock(UserRoleService::class);
        $this->budgetSubmissionService = new BudgetSubmissionServiceImpl(
            $this->userRoleServiceMock,
            new BudgetLedgerServiceImpl()
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_get_index_data_for_admin_returns_all_divisions()
    {
        $admin = User::factory()->create();
        $this->createDivision('IT Division');
        $this->createDivision('Finance Division');
        $this->createDivision('Operation Division');

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
        $allowedDivisions = collect([
            $this->createDivision('IT Division'),
            $this->createDivision('Finance Division'),
        ]);
        $otherDivision = $this->createDivision('Operation Division');

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
        $this->assertEquals(3, $data['sourceDivisions']->count());
        $this->assertTrue($data['sourceDivisions']->contains('id', $otherDivision->id));
    }

    public function test_store_creates_submission()
    {
        $user = User::factory()->create();
        $division = $this->createDivision('IT Division');
        $workPlan = $this->createWorkPlan();
        $budgetItem = $this->createBudgetItem($workPlan);

        $dto = BudgetSubmissionData::fromArray([
            'division_id' => $division->id,
            'submission_date' => '2026-06-02',
            'type' => 'add',
            'work_plan_id' => $workPlan->id,
            'budget_account_id' => $budgetItem->id,
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

    public function test_approve_add_budget_records_source_debit_and_target_credit_amendment_mutations()
    {
        $user = User::factory()->create();
        $division = $this->createDivision('IT Division');
        $workPlan = $this->createWorkPlan();
        $sourceWorkPlan = $this->createWorkPlan('Source Workplan Test');
        $sourceBudgetItem = $this->createBudgetItem($sourceWorkPlan, 'Source item');
        $targetBudgetItem = $this->createBudgetItem($workPlan, 'Target item');
        $this->recordInitialBudget($sourceBudgetItem, 1000000);

        $submission = BudgetSubmission::create([
            'user_id' => $user->id,
            'division_id' => $division->id,
            'division_name' => $division->name,
            'work_plan_id' => $workPlan->id,
            'submission_date' => '2026-06-02',
            'type' => 'add',
            'source_budget_account_id' => $sourceBudgetItem->id,
            'budget_account_id' => $targetBudgetItem->id,
            'estimation_amount' => 750000,
            'description' => 'Top up target item',
            'status' => 0,
        ]);

        $this->budgetSubmissionService->approve($submission->id);

        $this->assertDatabaseHas('budget_submissions', [
            'id' => $submission->id,
            'status' => 1,
        ]);
        $this->assertDatabaseHas('budget_mutations', [
            'budget_submission_id' => $submission->id,
            'workplan_budget_item_id' => $sourceBudgetItem->id,
            'mutation_type' => BudgetMutation::TYPE_DEBIT,
            'category' => BudgetMutation::CATEGORY_BUDGET_AMENDMENT,
            'amount' => 750000,
        ]);
        $this->assertDatabaseHas('budget_mutations', [
            'budget_submission_id' => $submission->id,
            'workplan_budget_item_id' => $targetBudgetItem->id,
            'mutation_type' => BudgetMutation::TYPE_CREDIT,
            'category' => BudgetMutation::CATEGORY_BUDGET_AMENDMENT,
            'amount' => 750000,
        ]);
    }

    public function test_approve_add_budget_without_source_budget_account_fails()
    {
        $user = User::factory()->create();
        $division = $this->createDivision('IT Division');
        $workPlan = $this->createWorkPlan();
        $targetBudgetItem = $this->createBudgetItem($workPlan, 'Target item');

        $submission = BudgetSubmission::create([
            'user_id' => $user->id,
            'division_id' => $division->id,
            'division_name' => $division->name,
            'work_plan_id' => $workPlan->id,
            'submission_date' => '2026-06-02',
            'type' => 'add',
            'budget_account_id' => $targetBudgetItem->id,
            'estimation_amount' => 750000,
            'description' => 'Top up target item',
            'status' => 0,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Budget item sumber wajib dipilih untuk Add Budget.');

        $this->budgetSubmissionService->approve($submission->id);
    }

    public function test_final_approval_can_adjust_budget_movement_amount_and_records_change()
    {
        $logService = Mockery::mock(LogService::class);
        $approvalService = new BudgetSubmissionApprovalServiceImpl(
            $logService,
            new BudgetLedgerServiceImpl()
        );

        $user = User::factory()->create();
        $division = $this->createDivision('IT Division');
        $targetWorkPlan = $this->createWorkPlan('Target Workplan Test');
        $sourceWorkPlan = $this->createWorkPlan('Source Workplan Test');
        $sourceBudgetItem = $this->createBudgetItem($sourceWorkPlan, 'Source item');
        $targetBudgetItem = $this->createBudgetItem($targetWorkPlan, 'Target item');
        $this->recordInitialBudget($sourceBudgetItem, 1000000);

        $submission = BudgetSubmission::create([
            'user_id' => $user->id,
            'division_id' => $division->id,
            'division_name' => $division->name,
            'work_plan_id' => $targetWorkPlan->id,
            'submission_date' => '2026-06-02',
            'type' => 'add',
            'budget_account_id' => $targetBudgetItem->id,
            'estimation_amount' => 750000,
            'description' => 'Top up target item',
            'status' => 0,
        ]);

        $moduleId = DB::table('approval_modules')->insertGetId([
            'module_name' => 'Budget Submission',
            'table_name' => 'budget_submissions',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $templateId = DB::table('approval_flow_templates')->insertGetId([
            'module_id' => $moduleId,
            'template_name' => 'Budget Submission Flow',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $requesterEmploymentId = $this->createEmployment('Requester');
        $approverEmploymentId = $this->createEmployment('Final Approver');

        $requestId = DB::table('approval_requests')->insertGetId([
            'module_id' => $moduleId,
            'reference_id' => $submission->id,
            'reference_number' => 'BS-APR-AMOUNT',
            'template_id' => $templateId,
            'template_snapshot' => json_encode([]),
            'status' => 'pending',
            'current_phase' => 'master_flow',
            'current_level' => 1,
            'total_levels' => 1,
            'requester_id' => $requesterEmploymentId,
            'requested_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $detailId = DB::table('approval_request_details')->insertGetId([
            'request_id' => $requestId,
            'phase' => 'master_flow',
            'level_sequence' => 1,
            'employment_id' => $approverEmploymentId,
            'employment_name' => 'Final Approver',
            'status' => 'pending',
            'approved_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = $approvalService->processApproval(
            $detailId,
            'approve',
            $approverEmploymentId,
            null,
            $sourceBudgetItem->id,
            600000
        );

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('budget_submissions', [
            'id' => $submission->id,
            'status' => 1,
            'source_budget_account_id' => $sourceBudgetItem->id,
            'approved_amount' => 600000,
            'approved_amount_changed_by' => $approverEmploymentId,
        ]);
        $this->assertNotNull($submission->fresh()->approved_amount_changed_at);
        $this->assertDatabaseHas('budget_mutations', [
            'budget_submission_id' => $submission->id,
            'workplan_budget_item_id' => $sourceBudgetItem->id,
            'mutation_type' => BudgetMutation::TYPE_DEBIT,
            'amount' => 600000,
        ]);
        $this->assertDatabaseHas('budget_mutations', [
            'budget_submission_id' => $submission->id,
            'workplan_budget_item_id' => $targetBudgetItem->id,
            'mutation_type' => BudgetMutation::TYPE_CREDIT,
            'amount' => 600000,
        ]);
    }

    public function test_approve_relocation_records_source_debit_and_target_credit_mutations()
    {
        $user = User::factory()->create();
        $division = $this->createDivision('IT Division');
        $workPlan = $this->createWorkPlan();
        $sourceBudgetItem = $this->createBudgetItem($workPlan, 'Source item');
        $targetBudgetItem = $this->createBudgetItem($workPlan, 'Target item');
        $this->recordInitialBudget($sourceBudgetItem, 1000000);

        $submission = BudgetSubmission::create([
            'user_id' => $user->id,
            'division_id' => $division->id,
            'division_name' => $division->name,
            'work_plan_id' => $workPlan->id,
            'submission_date' => '2026-06-02',
            'type' => 'relocation',
            'source_budget_account_id' => $sourceBudgetItem->id,
            'budget_account_id' => $targetBudgetItem->id,
            'estimation_amount' => 300000,
            'description' => 'Move budget to target',
            'status' => 0,
        ]);

        $this->budgetSubmissionService->approve($submission->id);

        $this->assertDatabaseHas('budget_mutations', [
            'budget_submission_id' => $submission->id,
            'workplan_budget_item_id' => $sourceBudgetItem->id,
            'mutation_type' => BudgetMutation::TYPE_DEBIT,
            'category' => BudgetMutation::CATEGORY_BUDGET_RELOCATION_OUT,
            'amount' => 300000,
        ]);
        $this->assertDatabaseHas('budget_mutations', [
            'budget_submission_id' => $submission->id,
            'workplan_budget_item_id' => $targetBudgetItem->id,
            'mutation_type' => BudgetMutation::TYPE_CREDIT,
            'category' => BudgetMutation::CATEGORY_BUDGET_RELOCATION_IN,
            'amount' => 300000,
        ]);
    }

    public function test_get_approval_timeline_for_submission_returns_all_approvers_and_current_stage()
    {
        $logService = Mockery::mock(LogService::class);
        $approvalService = new BudgetSubmissionApprovalServiceImpl(
            $logService,
            new BudgetLedgerServiceImpl()
        );

        $user = User::factory()->create();
        $division = $this->createDivision('IT Division');
        $workPlan = $this->createWorkPlan();
        $budgetItem = $this->createBudgetItem($workPlan, 'Timeline item');
        $submission = BudgetSubmission::create([
            'user_id' => $user->id,
            'division_id' => $division->id,
            'division_name' => $division->name,
            'work_plan_id' => $workPlan->id,
            'submission_date' => '2026-06-02',
            'type' => 'add',
            'budget_account_id' => $budgetItem->id,
            'estimation_amount' => 1000000,
            'description' => 'Timeline submission',
            'status' => 0,
        ]);

        $moduleId = DB::table('approval_modules')->insertGetId([
            'module_name' => 'Budget Submission',
            'table_name' => 'budget_submissions',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $templateId = DB::table('approval_flow_templates')->insertGetId([
            'module_id' => $moduleId,
            'template_name' => 'Budget Submission Flow',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $requesterEmploymentId = $this->createEmployment('Requester');
        $firstApproverEmploymentId = $this->createEmployment('First Approver');
        $secondApproverEmploymentId = $this->createEmployment('Second Approver');
        $thirdApproverEmploymentId = $this->createEmployment('Third Approver');

        $requestId = DB::table('approval_requests')->insertGetId([
            'module_id' => $moduleId,
            'reference_id' => $submission->id,
            'reference_number' => 'BS-APR-TEST',
            'template_id' => $templateId,
            'template_snapshot' => json_encode([]),
            'status' => 'pending',
            'current_phase' => 'uppline',
            'current_level' => 2,
            'total_levels' => 3,
            'requester_id' => $requesterEmploymentId,
            'requested_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('approval_request_details')->insert([
            [
                'request_id' => $requestId,
                'phase' => 'uppline',
                'level_sequence' => 1,
                'employment_id' => $firstApproverEmploymentId,
                'employment_name' => 'First Approver',
                'status' => 'approved',
                'approved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'request_id' => $requestId,
                'phase' => 'uppline',
                'level_sequence' => 2,
                'employment_id' => $secondApproverEmploymentId,
                'employment_name' => 'Second Approver',
                'status' => 'pending',
                'approved_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'request_id' => $requestId,
                'phase' => 'master_flow',
                'level_sequence' => 3,
                'employment_id' => $thirdApproverEmploymentId,
                'employment_name' => 'Third Approver',
                'status' => 'pending',
                'approved_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $result = $approvalService->getApprovalTimelineForSubmission($submission->id);

        $this->assertTrue($result['success']);
        $this->assertSame('BS-APR-TEST', $result['data']['reference_number']);
        $this->assertSame('Second Approver', $result['data']['current_approver']);
        $this->assertCount(3, $result['data']['timeline']);
        $this->assertSame('approved', $result['data']['timeline'][0]['status']);
        $this->assertSame('pending', $result['data']['timeline'][1]['status']);
    }

    private function createDivision(string $name): Division
    {
        return Division::create([
            'name' => $name,
            'status' => 'active',
        ]);
    }

    private function createWorkPlan(string $activity = 'Workplan Test'): KPIWorkPlan
    {
        return KPIWorkPlan::create([
            'kpi_type' => 'department',
            'kpi_id' => 1,
            'year' => 2026,
            'activity' => $activity,
            'status' => 'approved',
        ]);
    }

    private function createBudgetItem(KPIWorkPlan $workPlan, string $description = 'Budget item'): WorkplanBudgetItem
    {
        $category = BudgetCategory::create([
            'code' => uniqid('CAT'),
            'name' => 'Category Test',
        ]);
        $budgetCode = uniqid('BUD');

        DB::table('budget_codes')->insert([
            'code' => $budgetCode,
            'name' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return WorkplanBudgetItem::create([
            'kpi_workplan_id' => $workPlan->id,
            'budget_category_id' => $category->id,
            'description' => $description,
            'budget_code' => $budgetCode,
            'total' => 1000000,
            'status' => 'approved',
        ]);
    }

    private function recordInitialBudget(WorkplanBudgetItem $budgetItem, int $amount): void
    {
        BudgetMutation::create([
            'workplan_budget_item_id' => $budgetItem->id,
            'transaction_id' => null,
            'transaction_detail_id' => null,
            'transaction_lpj_submission_id' => null,
            'mutation_type' => BudgetMutation::TYPE_CREDIT,
            'amount' => $amount,
            'category' => BudgetMutation::CATEGORY_INITIAL_BUDGET,
            'description' => 'Initial test budget',
            'created_at' => now(),
        ]);
    }

    private function createEmployment(string $name): int
    {
        $employeeId = DB::table('employee')->insertGetId([
            'first_name' => $name,
            'last_name' => '',
            'email' => strtolower(str_replace(' ', '.', $name)) . '@example.test',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('employment')->insertGetId([
            'employee_id' => $employeeId,
            'job_level_name' => 'Manager',
            'employment_status' => 'active',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
