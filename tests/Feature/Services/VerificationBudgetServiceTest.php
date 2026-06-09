<?php

namespace Tests\Feature\Services;

use App\Models\BudgetCategory;
use App\Models\Employee;
use App\Models\KPIWorkPlan;
use App\Models\WorkplanBudgetApprover;
use App\Models\WorkplanBudgetItem;
use App\Services\NotificationService;
use App\Services\VerificationBudgetService\VerificationBudgetServiceImpl;
use App\Services\WorkplanBudgetItemApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class VerificationBudgetServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createVerifier(): Employee
    {
        return Employee::create([
            'email' => 'verifier@example.test',
            'password' => bcrypt('password'),
            'first_name' => 'Verifier',
            'last_name' => 'Budget',
            'status' => 'active',
        ]);
    }

    private function createPendingVerificationItem(): WorkplanBudgetItem
    {
        $workplan = KPIWorkPlan::create([
            'kpi_type' => 'department',
            'kpi_id' => 1,
            'year' => (int) now()->year,
            'activity' => 'Verification Workplan',
            'status' => 'approved',
        ]);

        $category = BudgetCategory::create([
            'code' => '2',
            'name' => 'Investment',
            'level' => 1,
            'is_active' => true,
        ]);

        return WorkplanBudgetItem::create([
            'kpi_workplan_id' => $workplan->id,
            'budget_category_id' => $category->id,
            'description' => 'Pending Verification Item',
            'total' => 1000000,
            'activity_jan' => 2,
            'activity_feb' => 1,
            'status' => 'draft',
            'category_type' => 'Routine',
            'price_estimation' => 250000,
            'price_final' => 0,
            'verification_status' => 'pending',
            'cost_center' => '6220',
            'budget_code' => '1-1210-2-0-00-00-001',
        ]);
    }

    public function test_verify_budget_returns_approval_debug_reference_when_auto_submit_fails(): void
    {
        $verifier = $this->createVerifier();
        $item = $this->createPendingVerificationItem();

        WorkplanBudgetApprover::create([
            'workplan_budget_item_id' => $item->id,
            'verifier_id' => $verifier->id,
            'is_executor' => false,
        ]);

        $approvalService = Mockery::mock(WorkplanBudgetItemApprovalService::class);
        $approvalService->shouldReceive('submitForApproval')
            ->once()
            ->with($item->id)
            ->andReturn([
                'success' => false,
                'message' => 'Tidak ada approver yang sesuai untuk request ini.',
                'debug_ref' => 'APR-TRACE-001',
                'data' => [
                    'debug_ref' => 'APR-TRACE-001',
                ],
            ]);

        $notificationService = Mockery::mock(NotificationService::class);

        $service = new VerificationBudgetServiceImpl($approvalService, $notificationService);

        $this->actingAs($verifier);

        $result = $service->verifyBudget($item->id, 500000, 'Harga sudah dicek');

        $item->refresh();

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('auto-submit approval gagal', $result['message']);
        $this->assertFalse($result['data']['approval_submitted']);
        $this->assertSame('APR-TRACE-001', $result['data']['approval_debug_ref']);
        $this->assertSame('APR-TRACE-001', $result['debug_ref']);
        $this->assertSame('verified', $item->verification_status);
        $this->assertSame('500000', (string) $item->price_final);
        $this->assertSame('1500000.00', (string) $item->total);
        $this->assertDatabaseHas('workplan_budget_verifications', [
            'workplan_budget_item_id' => $item->id,
            'verifier_id' => (string) $verifier->id,
            'verified_price_total' => 1500000,
        ]);
        $this->assertDatabaseHas('workplan_budget_approver', [
            'workplan_budget_item_id' => $item->id,
            'verifier_id' => (string) $verifier->id,
            'is_executor' => true,
        ]);
    }
}
