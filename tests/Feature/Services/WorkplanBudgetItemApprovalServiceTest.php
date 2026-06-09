<?php

namespace Tests\Feature\Services;

use App\Models\ApprovalFlowTemplate;
use App\Models\ApprovalModule;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestDetail;
use App\Models\BudgetCategory;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\KPIWorkPlan;
use App\Models\Notification;
use App\Models\NotificationCategory;
use App\Models\WorkplanBudgetItem;
use App\Services\WorkplanBudgetItemApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkplanBudgetItemApprovalServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkplanBudgetItemApprovalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WorkplanBudgetItemApprovalService::class);
    }

    private function createEmployment(string $email): Employment
    {
        $employee = Employee::create([
            'email' => $email,
            'password' => bcrypt('password'),
            'first_name' => 'Approval',
            'last_name' => 'User',
            'status' => 'active',
        ]);

        return Employment::updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'job_level_id' => '2',
                'job_level_name' => 'Division',
                'job_position_name' => 'Approver',
                'status' => 'active',
            ],
        );
    }

    private function createModuleAndTemplate(string $tableName): array
    {
        $module = ApprovalModule::create([
            'module_name' => $tableName,
            'table_name' => $tableName,
            'is_active' => true,
        ]);

        $template = ApprovalFlowTemplate::create([
            'module_id' => $module->id,
            'template_name' => 'Template ' . $tableName,
            'use_uppline_chain' => false,
            'use_threshold' => false,
            'priority' => 1,
            'is_active' => true,
        ]);

        return [$module, $template];
    }

    private function createPendingApprovalRequest(string $tableName, int $referenceId, Employment $approver): ApprovalRequest
    {
        [$module, $template] = $this->createModuleAndTemplate($tableName);

        $request = ApprovalRequest::create([
            'module_id' => $module->id,
            'reference_id' => $referenceId,
            'reference_number' => strtoupper($tableName) . '-' . $referenceId,
            'template_id' => $template->id,
            'template_snapshot' => json_encode([]),
            'status' => 'pending',
            'current_phase' => 'uppline',
            'current_level' => 1,
            'total_levels' => 1,
            'requester_id' => $approver->id,
            'requested_at' => now(),
        ]);

        ApprovalRequestDetail::create([
            'request_id' => $request->id,
            'phase' => 'uppline',
            'level_sequence' => 1,
            'employment_id' => $approver->id,
            'employment_name' => 'Approval User',
            'status' => 'pending',
        ]);

        return $request;
    }

    private function createWorkplanBudgetItem(): WorkplanBudgetItem
    {
        $workplan = KPIWorkPlan::create([
            'kpi_type' => 'department',
            'kpi_id' => 1,
            'year' => (int) now()->year,
            'activity' => 'Current Workplan',
            'status' => 'approved',
        ]);

        $category = BudgetCategory::create([
            'code' => '1',
            'name' => 'Routine',
            'level' => 1,
            'is_active' => true,
        ]);

        return WorkplanBudgetItem::create([
            'kpi_workplan_id' => $workplan->id,
            'budget_category_id' => $category->id,
            'description' => 'Actionable Budget Item',
            'total' => 5000000,
            'activity_jan' => 1,
            'status' => 'pending',
            'category_type' => 'Routine',
            'price_estimation' => 5000000,
            'price_final' => 5000000,
            'verification_status' => 'verified',
        ]);
    }

    private function createApprovalTaskNotification(Employment $employment, ?int $referenceId): Notification
    {
        $category = NotificationCategory::firstOrCreate(['name' => 'approval']);

        return Notification::create([
            'employee_id' => $employment->employee_id,
            'category_id' => $category->id,
            'title' => 'Permintaan Approval Workplan Budget',
            'details' => 'Ada permintaan approval baru untuk Workplan Budget Item: Actionable Budget Item',
            'reference_type' => $referenceId ? 'workplan_budget_item_approval' : null,
            'reference_id' => $referenceId,
        ]);
    }

    public function test_pending_approvals_excludes_other_modules_and_deletes_stale_task_notifications(): void
    {
        $approver = $this->createEmployment('stale-approval@example.test');
        $this->createPendingApprovalRequest('transactions', 12345, $approver);
        $referencedNotification = $this->createApprovalTaskNotification($approver, 99999);
        $legacyNotification = $this->createApprovalTaskNotification($approver, null);

        $result = $this->service->getPendingApprovalsForUser($approver->id);

        $this->assertTrue($result['success']);
        $this->assertSame(0, $result['count']);
        $this->assertSame([], $result['data']);
        $this->assertDatabaseMissing('notifications', ['id' => $referencedNotification->id]);
        $this->assertDatabaseMissing('notifications', ['id' => $legacyNotification->id]);
    }

    public function test_pending_approvals_keeps_actionable_workplan_budget_item_notifications(): void
    {
        $approver = $this->createEmployment('active-approval@example.test');
        $item = $this->createWorkplanBudgetItem();
        $this->createPendingApprovalRequest('workplan_budget_items', $item->id, $approver);
        $notification = $this->createApprovalTaskNotification($approver, $item->id);

        $result = $this->service->getPendingApprovalsForUser($approver->id);

        $this->assertTrue($result['success']);
        $this->assertSame(1, $result['count']);
        $this->assertSame($item->id, $result['data'][0]['item']['id']);
        $this->assertDatabaseHas('notifications', ['id' => $notification->id]);
    }

    public function test_submit_for_approval_returns_debug_reference_when_module_is_missing(): void
    {
        $approver = $this->createEmployment('missing-module@example.test');
        $item = $this->createWorkplanBudgetItem();

        $this->actingAs($approver->employee);

        $result = $this->service->submitForApproval($item->id);

        $this->assertFalse($result['success']);
        $this->assertSame('Approval module untuk workplan_budget_items belum dikonfigurasi.', $result['message']);
        $this->assertNotEmpty($result['debug_ref'] ?? null);
        $this->assertSame($result['debug_ref'], $result['data']['debug_ref'] ?? null);
    }
}
