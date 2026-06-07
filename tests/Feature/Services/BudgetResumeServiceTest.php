<?php

namespace Tests\Feature\Services;

use App\Models\BudgetCategory;
use App\Models\BudgetCode;
use App\Models\BudgetMutation;
use App\Models\CompanyPolicy;
use App\Models\CompanyPolicyDetail;
use App\Models\Department;
use App\Models\Division;
use App\Models\KPIDepartment;
use App\Models\KPIDivision;
use App\Models\KPIWorkPlan;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\WorkplanBudgetItem;
use App\Services\BudgetResumeService\BudgetResumeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetResumeServiceTest extends TestCase
{
    use RefreshDatabase;

    private BudgetResumeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BudgetResumeService::class);
    }

    private function createDepartmentWorkplan(Division $division, int $year): KPIWorkPlan
    {
        $department = Department::create([
            'division_id' => $division->id,
            'name' => 'Finance',
            'status' => 'active',
        ]);

        $policy = CompanyPolicy::create([
            'tahun' => $year,
            'nama_dokumen' => 'Policy ' . $year,
            'file_path' => 'company-policy/' . $year . '.pdf',
        ]);

        $policyDetail = CompanyPolicyDetail::create([
            'company_policy_id' => $policy->id,
            'strategic_goal' => 'Goal ' . $year,
            'description' => 'Desc',
            'target' => 'Target',
            'strategic_goal_id' => 'Goal Source ' . $year,
            'description_id' => 'Description Source ' . $year,
        ]);

        $kpiDivision = KPIDivision::create([
            'company_policy_detail_id' => $policyDetail->id,
            'division_id' => $division->id,
            'year' => $year,
            'division_goals' => 'Division Goals ' . $year,
        ]);

        $kpiDepartment = KPIDepartment::create([
            'kpi_division_id' => $kpiDivision->id,
            'department_id' => $department->id,
            'year' => $year,
            'department_goals' => 'Department Goals ' . $year,
        ]);

        return KPIWorkPlan::create([
            'kpi_type' => 'department',
            'kpi_id' => $kpiDepartment->id,
            'year' => $year,
            'activity' => 'Budget Control Program',
            'status' => 'approved',
        ]);
    }

    private function createBudgetItem(KPIWorkPlan $workplan, BudgetCategory $category, string $budgetCode): WorkplanBudgetItem
    {
        return WorkplanBudgetItem::create([
            'kpi_workplan_id' => $workplan->id,
            'budget_category_id' => $category->id,
            'description' => 'Office Supplies',
            'budget_code' => $budgetCode,
            'total' => 999999,
            'activity_jan' => 99,
            'status' => 'approved',
            'category_type' => 'Routine',
            'verification_status' => 'verified',
            'price_estimation' => 1000,
            'price_final' => 1000,
        ]);
    }

    private function createMutation(WorkplanBudgetItem $item, string $type, float $amount, string $category, string $date): void
    {
        BudgetMutation::create([
            'workplan_budget_item_id' => $item->id,
            'mutation_type' => $type,
            'amount' => $amount,
            'category' => $category,
            'description' => $category,
            'created_at' => $date,
        ]);
    }

    private function createTransactionDetail(WorkplanBudgetItem $item, int $year, int $status, ?string $approvalStatus, float $amount): void
    {
        $transaction = Transaction::create([
            'transaction_date' => $year . '-04-10',
            'user_id' => 1,
            'user_name' => 'Requester',
            'unit_id' => 1,
            'unit_name' => 'PCS',
            'program_id' => $item->kpi_workplan_id,
            'purpose' => 'Submission ' . $amount,
            'estimated_amount' => $amount,
            'actual_amount' => 0,
            'urgency' => 'medium',
            'status' => $status,
            'status_approval' => $approvalStatus,
        ]);

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'budget_id' => $item->id,
            'budget_name' => $item->description,
            'goods_service_name' => 'Goods',
            'estimated_price' => $amount,
            'estimated_quantity' => 1,
            'estimated_total' => $amount,
            'unit_id' => 1,
            'unit_name' => 'PCS',
            'urgency' => 'medium',
        ]);
    }

    public function test_budget_resume_uses_ledger_mutations_and_pending_approval_submissions(): void
    {
        $year = (int) now()->year;
        $division = Division::create(['name' => 'Finance Division', 'status' => 'active']);
        $workplan = $this->createDepartmentWorkplan($division, $year);
        $category = BudgetCategory::create(['code' => '1', 'name' => 'Routine', 'level' => 1, 'is_active' => true]);
        BudgetCode::create(['budget_code' => 'BC-100', 'name' => 'Office Budget', 'active_flag' => 1]);
        $item = $this->createBudgetItem($workplan, $category, 'BC-100');

        $this->createMutation($item, BudgetMutation::TYPE_CREDIT, 1000, BudgetMutation::CATEGORY_INITIAL_BUDGET, $year . '-01-05 00:00:00');
        $this->createMutation($item, BudgetMutation::TYPE_CREDIT, 200, BudgetMutation::CATEGORY_BUDGET_AMENDMENT, $year . '-01-06 00:00:00');
        $this->createMutation($item, BudgetMutation::TYPE_DEBIT, 100, BudgetMutation::CATEGORY_BUDGET_RELOCATION_OUT, $year . '-01-07 00:00:00');
        $this->createMutation($item, BudgetMutation::TYPE_DEBIT, 300, BudgetMutation::CATEGORY_CASH_ADVANCE, $year . '-02-01 00:00:00');
        $this->createMutation($item, BudgetMutation::TYPE_CREDIT, 50, BudgetMutation::CATEGORY_LPJ_REFUND, $year . '-02-10 00:00:00');
        $this->createMutation($item, BudgetMutation::TYPE_DEBIT, 20, BudgetMutation::CATEGORY_LPJ_REIMBURSE, $year . '-03-10 00:00:00');

        $this->createTransactionDetail($item, $year, Transaction::STATUS_PROGRESS, Transaction::APPROVAL_STATUS_PENDING, 120);
        $this->createTransactionDetail($item, $year, Transaction::STATUS_APPROVED, Transaction::APPROVAL_STATUS_APPROVED, 999);
        $this->createTransactionDetail($item, $year, Transaction::STATUS_SUBMISSION, Transaction::APPROVAL_STATUS_PENDING, 777);

        $data = $this->service->getPageData([
            'year' => $year,
            'division_id' => $division->id,
            'category_id' => $category->id,
            'budget_code' => 'BC-100',
        ]);

        $this->assertSame(1100.0, $data['summary']['total_budget']);
        $this->assertSame(270.0, $data['summary']['total_realization']);
        $this->assertSame(120.0, $data['summary']['total_submission']);
        $this->assertSame(710.0, $data['summary']['total_balance']);

        $itemRow = $data['budgetData']['Finance Division'][0];
        $this->assertSame(1100.0, $itemRow['total']);
        $this->assertSame(270.0, $itemRow['realization']);
        $this->assertSame(120.0, $itemRow['total_submission']);
        $this->assertSame(710.0, $itemRow['balance']);
        $this->assertSame(1100.0, $itemRow['months']['JAN']['budget']);
        $this->assertSame(250.0, $itemRow['months']['FEB']['realization']);
        $this->assertSame(20.0, $itemRow['months']['MAR']['realization']);
        $this->assertSame(120.0, $itemRow['months']['APR']['submission']);
    }

    public function test_budget_resume_budget_code_search_is_paginated_and_excludes_inactive_codes(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            BudgetCode::create([
                'budget_code' => sprintf('BC-PAGE-%02d', $i),
                'name' => 'Paged Budget ' . $i,
                'active_flag' => 1,
            ]);
        }

        BudgetCode::create([
            'budget_code' => 'BC-PAGE-INACTIVE',
            'name' => 'Inactive Budget',
            'active_flag' => 0,
        ]);

        $pageOne = $this->service->searchBudgetCodes('BC-PAGE', 10, 1);
        $pageTwo = $this->service->searchBudgetCodes('BC-PAGE', 10, 2);

        $this->assertTrue($pageOne['success']);
        $this->assertSame(15, $pageOne['total']);
        $this->assertTrue($pageOne['has_more']);
        $this->assertCount(10, $pageOne['data']);
        $this->assertSame('BC-PAGE-01', $pageOne['data']->first()['budget_code']);

        $this->assertTrue($pageTwo['success']);
        $this->assertSame(15, $pageTwo['total']);
        $this->assertFalse($pageTwo['has_more']);
        $this->assertCount(5, $pageTwo['data']);
        $this->assertNotContains(
            'BC-PAGE-INACTIVE',
            $pageOne['data']->merge($pageTwo['data'])->pluck('budget_code')->all()
        );
    }

    public function test_budget_resume_budget_code_lookup_returns_only_active_exact_code(): void
    {
        BudgetCode::create([
            'budget_code' => 'BC-LOOKUP',
            'name' => 'Lookup Budget',
            'active_flag' => 1,
        ]);

        BudgetCode::create([
            'budget_code' => 'BC-HIDDEN',
            'name' => 'Hidden Budget',
            'active_flag' => 0,
        ]);

        $active = $this->service->getBudgetCodeByCode('BC-LOOKUP');
        $inactive = $this->service->getBudgetCodeByCode('BC-HIDDEN');

        $this->assertTrue($active['success']);
        $this->assertSame('BC-LOOKUP', $active['data']['budget_code']);
        $this->assertSame('BC-LOOKUP - Lookup Budget', $active['data']['text']);
        $this->assertTrue($inactive['success']);
        $this->assertNull($inactive['data']);
    }
}
