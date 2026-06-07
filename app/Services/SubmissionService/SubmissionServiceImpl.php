<?php

namespace App\Services\SubmissionService;

use App\Models\WorkplanBudgetItem;
use App\Models\Unit;
use App\Models\KPIWorkPlan;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\JobLevel;
use App\Models\JobPosition;
use App\Models\Employment;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestDetail;
use App\Services\ApprovalTransactionService\ApprovalTransactionService;
use App\Services\BudgetLedgerService\BudgetLedgerService;
use App\Services\LogService\LogService;
use App\Services\UserRoleService\UserRoleService;
use App\Exports\SubmissionTemplateExport;
use App\Imports\SubmissionImport;
use App\Imports\MacframeImport;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SubmissionServiceImpl implements SubmissionService
{
    public function __construct(
        private readonly Transaction $model,
        private readonly ApprovalTransactionService $approvalTransactionService,
        private readonly BudgetLedgerService $budgetLedgerService,
        private readonly LogService $logService,
        private readonly UserRoleService $userRoleService,
    ) {}

    /* ========================
        VIEW / PAGE DATA
    ======================== */

    public function getUserPageData(): array
    {
        $userId = Auth::id();
        $employee = Auth::user();
        $employment = $employee->employment;
        $isAdmin = $this->userRoleService->isAdmin($employee);

        $newSubmission = $this->model->where('user_id', $userId)
            ->where('status', Transaction::STATUS_SUBMISSION)->count();
        $progress = $this->model->where('user_id', $userId)
            ->where('status', Transaction::STATUS_PROGRESS)->count();
        $approved = $this->model->where('user_id', $userId)
            ->where('status', Transaction::STATUS_APPROVED)->count();
        $paid = $this->model->where('user_id', $userId)
            ->where('status', Transaction::STATUS_PAID)->count();
        $totalSubmission = $this->model->where('user_id', $userId)->count();

        $years = $this->model->selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $statuses = [
            ['value' => Transaction::STATUS_SUBMISSION, 'label' => 'Submission'],
            ['value' => Transaction::STATUS_PROGRESS, 'label' => 'Progress'],
            ['value' => Transaction::STATUS_APPROVED, 'label' => 'Approved'],
            ['value' => Transaction::STATUS_PAID, 'label' => 'Paid'],
            ['value' => Transaction::STATUS_COMPLETED, 'label' => 'Completed'],
            ['value' => Transaction::STATUS_REJECTED, 'label' => 'Rejected'],
            ['value' => Transaction::STATUS_CANCELLED, 'label' => 'Cancelled'],
        ];

        $jobLevels = JobLevel::all();
        $jobPositions = JobPosition::all();

        $currentYear = (int) now()->year;

        $workplansQuery = KPIWorkPlan::with(['KPIDepartment', 'kpiSection'])
            ->where('year', $currentYear);
        if (! $isAdmin) {
            $divisionIds = $this->userRoleService->getDivisionIds($employee);
            $workplansQuery->whereDivisionIn($divisionIds);
        }
        $workplans = $workplansQuery->get();

        $budgetCodes = WorkplanBudgetItem::with('budgetCodeRelation')->get();
        $units = Unit::all();

        return [
            'newSubmission' => $newSubmission,
            'progress' => $progress,
            'approved' => $approved,
            'paid' => $paid,
            'totalSubmission' => $totalSubmission,
            'years' => $years,
            'statuses' => $statuses,
            'jobLevels' => $jobLevels,
            'jobPositions' => $jobPositions,
            'workplans' => $workplans,
            'budgetCodes' => $budgetCodes,
            'units' => $units,
            'employment' => $employment,
        ];
    }

    public function getApprovalPageData(): array
    {
        $userId = Auth::id();
        $employment = Employment::where('employee_id', $userId)->get();

        $newSubmission = $this->model->where('user_id', $userId)
            ->where('status', Transaction::STATUS_SUBMISSION)->count();
        $progress = $this->model->where('user_id', $userId)
            ->where('status', Transaction::STATUS_PROGRESS)->count();
        $approved = $this->model->where('user_id', $userId)
            ->where('status', Transaction::STATUS_APPROVED)->count();
        $paid = $this->model->where('user_id', $userId)
            ->where('status', Transaction::STATUS_PAID)->count();
        $totalSubmission = $this->model->where('user_id', $userId)->count();

        $years = $this->model->selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $statuses = [
            ['value' => Transaction::STATUS_SUBMISSION, 'label' => 'Submission'],
            ['value' => Transaction::STATUS_PROGRESS, 'label' => 'Progress'],
            ['value' => Transaction::STATUS_APPROVED, 'label' => 'Approved'],
            ['value' => Transaction::STATUS_PAID, 'label' => 'Paid'],
            ['value' => Transaction::STATUS_COMPLETED, 'label' => 'Completed'],
            ['value' => Transaction::STATUS_REJECTED, 'label' => 'Rejected'],
            ['value' => Transaction::STATUS_CANCELLED, 'label' => 'Cancelled'],
        ];

        $jobLevels = JobLevel::all();
        $jobPositions = JobPosition::all();
        $workplans = KPIWorkPlan::with(['KPIDepartment', 'kpiSection'])->get();
        $budgetCodes = WorkplanBudgetItem::with('budgetCodeRelation')->get();
        $units = Unit::all();

        return [
            'newSubmission' => $newSubmission,
            'progress' => $progress,
            'approved' => $approved,
            'paid' => $paid,
            'totalSubmission' => $totalSubmission,
            'years' => $years,
            'statuses' => $statuses,
            'jobLevels' => $jobLevels,
            'jobPositions' => $jobPositions,
            'workplans' => $workplans,
            'budgetCodes' => $budgetCodes,
            'units' => $units,
            'employment' => $employment,
        ];
    }

    /* ========================
        SUMMARY / DATA
    ======================== */

    public function getSummary(array $filters = []): array
    {
        $userId = Auth::id();
        $yearFilter = ! empty($filters['year']) && $filters['year'] !== 'all';
        $year = $filters['year'] ?? null;

        $requestCount = $this->model->where('user_id', $userId)
            ->whereIn('status', [
                Transaction::STATUS_SUBMISSION,
                Transaction::STATUS_PROGRESS,
            ])
            ->when($yearFilter, fn($q) => $q->whereYear('transaction_date', $year))
            ->count();

        $approved = $this->model->where('user_id', $userId)
            ->where('status', Transaction::STATUS_APPROVED)
            ->when($yearFilter, fn($q) => $q->whereYear('transaction_date', $year))
            ->count();

        $paid = $this->model->where('user_id', $userId)
            ->where('status', Transaction::STATUS_PAID)
            ->when($yearFilter, fn($q) => $q->whereYear('transaction_date', $year))
            ->count();

        $completion = $this->model->where('user_id', $userId)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->when($yearFilter, fn($q) => $q->whereYear('transaction_date', $year))
            ->count();

        $rejected = $this->model->where('user_id', $userId)
            ->where('status', Transaction::STATUS_REJECTED)
            ->when($yearFilter, fn($q) => $q->whereYear('transaction_date', $year))
            ->count();

        $totalSubmission = $this->model->where('user_id', $userId)->count();

        return [
            'success' => true,
            'data' => [
                'requestCount' => $requestCount,
                'paid' => $paid,
                'completion' => $completion,
                'rejected' => $rejected,
                'totalSubmission' => $totalSubmission,
            ],
        ];
    }

    public function getTransactions(array $filters = []): array
    {
        $userId = Auth::id();
        $user = Auth::user();
        $employment = $user->employment;
        $employmentId = $employment ? $employment->id : null;

        $query = $this->model->query()
            ->where('user_id', $userId)
            ->with([
                'details',
                'approvalRequest.details' => fn($q) => $q->orderBy('level_sequence'),
                'lpjSubmission',
            ]);

        // Filter by year
        $year = $filters['year'] ?? null;
        if ($year && $year !== '' && $year !== 'all') {
            $query->whereYear('transaction_date', $year);
        }

        // Filter by status
        $status = $filters['status'] ?? null;
        if ($status === 'request') {
            $query->whereIn('status', [0, 1, 2]);
        } elseif ($status !== null && $status !== '' && $status !== 'all') {
            $query->where('status', $status);
        }

        $perPage = $filters['per_page'] ?? 10;
        $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Add approval flags to each transaction
        $transactions->getCollection()->transform(function ($transaction) use ($employmentId) {
            $transaction->can_approve = false;
            $transaction->pending_approval = null;

            // Determine can_edit: editable if no approver has approved yet
            $hasApproved = $this->hasAnyApproverApproved($transaction);
            $transaction->can_edit = ! $hasApproved;
            $transaction->can_submit_lpj = $transaction->canSubmitLpj();

            // Check dynamic approval system
            if ($employmentId && $transaction->approvalRequest) {
                $request = $transaction->approvalRequest;
                if ($request->status === 'pending') {
                    $nextPending = $request->details
                        ->where('status', 'pending')
                        ->sortBy('level_sequence')
                        ->first();

                    if ($nextPending && $nextPending->employment_id == $employmentId) {
                        $transaction->can_approve = true;
                        $transaction->pending_approval_detail = $nextPending;
                    }
                }

                $transaction->approval_progress = [
                    'current_level' => $request->current_level,
                    'total_levels' => $request->total_levels,
                    'status' => $request->status,
                    'current_phase' => $request->current_phase,
                ];
            }

            return $transaction;
        });

        return [
            'success' => true,
            'data' => $transactions,
        ];
    }

    /* ========================
        TRANSACTION DETAIL
    ======================== */

    public function getTransactionDetail(int $id): array
    {
        $transaction = $this->model->with([
            'details',
            'approvalRequest.details' => fn($query) => $query->orderBy('phase')->orderBy('level_sequence'),
            'jobLevel',
            'jobPosition',
            'unit',
            'workplan',
            'lpjSubmission.approvalDetails.employment.employee',
        ])->findOrFail($id);

        $user = Auth::user();
        $isOwner = $transaction->user_id == $user->id;
        $isApprover = false;
        $canApprove = false;

        // Check if user is an approver
        if (! $isOwner && $user->employment) {
            $employmentId = $user->employment->id;

            $approvalDetail = ApprovalRequestDetail::whereHas('request', function ($q) use ($id) {
                $q->where('reference_id', $id)
                    ->whereHas('module', fn($mq) => $mq->where('table_name', 'transactions'));
            })
                ->where('employment_id', $employmentId)
                ->first();

            if ($approvalDetail) {
                $isApprover = true;
                $canApprove = $approvalDetail->status === 'pending';
            }
        }

        if (! $isOwner && ! $isApprover) {
            return [
                'success' => false,
                'message' => 'Unauthorized access',
                'status_code' => 403,
            ];
        }

        $transactionArray = $transaction->toArray();
        $transactionArray['can_approve'] = $canApprove;

        // Determine can_edit
        $hasApproved = $this->hasAnyApproverApproved($transaction);
        $transactionArray['can_edit'] = ! $hasApproved;

        // Determine status_approval
        if ($transaction->approvalRequest) {
            $transactionArray['status_approval'] = $transaction->approvalRequest->status;
        } else {
            $statusMap = [
                0 => 'pending',
                1 => 'pending',
                2 => 'pending',
                3 => 'pending',
                4 => 'pending',
                5 => 'pending',
                6 => 'rejected',
                7 => 'approved',
                8 => 'approved',
                -1 => 'cancelled',
            ];
            $transactionArray['status_approval'] = $statusMap[$transaction->status] ?? 'pending';
        }

        return [
            'success' => true,
            'data' => $transactionArray,
        ];
    }

    /* ========================
        CRUD OPERATIONS
    ======================== */

    public function createTransaction(array $data): array
    {
        // Validate budget items
        $budgetErrors = $this->validateBudgetItems($data['items']);
        if (! empty($budgetErrors)) {
            return [
                'success' => false,
                'message' => 'Budget validation failed. The following items exceed their budget values:',
                'budget_errors' => $budgetErrors,
                'status_code' => 422,
            ];
        }

        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            $estimatedAmount = 0;

            foreach ($data['items'] as $item) {
                $estimatedAmount += $item['quantity'] * $item['price'];
            }

            $unit = Unit::find($data['items'][0]['unit_id']);

            $transaction = $this->model->create([
                'transaction_date' => $data['transaction_date'],
                'planned_usage_date' => $data['planned_usage_date'] ?? null,
                'user_id' => $user->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'unit_id' => $unit->id ?? 0,
                'unit_name' => $unit->unit ?? '',
                'job_level_id' => $data['job_level_id'],
                'job_position_id' => $data['job_position_id'],
                'program_id' => $data['program_id'],
                'purpose' => $data['purpose'],
                'estimated_amount' => $estimatedAmount,
                'actual_amount' => 0,
                'urgency' => $data['urgency'],
                'status' => Transaction::STATUS_PENDING,
            ]);

            $this->createTransactionDetails($transaction->id, $data['items'], $data['urgency']);

            // Approval handling
            if (isset($data['auto_approve']) && $data['auto_approve']) {
                $approvalResult = $this->approvalTransactionService->autoApprove($transaction->id, 'Approve by McFramega');
            } else {
                // Submit for approval
                $approvalResult = $this->approvalTransactionService->submitForApproval($transaction->id);
            }

            if (! $approvalResult['success']) {
                Log::warning('Failed to process approval: ' . $approvalResult['message']);
                throw new \App\Exceptions\DomainException('Failed to process approval: ' . $approvalResult['message']);
            }

            return [
                'success' => true,
                'message' => 'Transaction created successfully' . ($approvalResult['success']
                    ? (isset($data['auto_approve']) && $data['auto_approve'] ? ' and auto-approved.' : ' and submitted for approval.')
                    : '. Note: ' . $approvalResult['message']),
                'data' => $transaction->load(['details', 'approvalRequest.details']),
                'approval' => $approvalResult,
            ];
        });
    }

    public function updateTransaction(int $id, array $data): array
    {
        // Validate budget items
        $budgetErrors = $this->validateBudgetItems($data['items']);
        if (! empty($budgetErrors)) {
            return [
                'success' => false,
                'message' => 'Budget validation failed. The following items exceed their budget values:',
                'budget_errors' => $budgetErrors,
                'status_code' => 422,
            ];
        }

        $transaction = $this->model->with(['approvalRequest.details'])->findOrFail($id);

        // Ownership check
        if ($transaction->user_id != Auth::id()) {
            return [
                'success' => false,
                'message' => 'Unauthorized access',
                'status_code' => 403,
            ];
        }

        // Edit guard: no approver has approved yet
        if ($this->hasAnyApproverApproved($transaction)) {
            return [
                'success' => false,
                'message' => 'Transaction cannot be edited because it has already been approved by one or more approvers.',
                'status_code' => 403,
            ];
        }

        return DB::transaction(function () use ($transaction, $data) {
            $estimatedAmount = 0;
            foreach ($data['items'] as $item) {
                $estimatedAmount += $item['quantity'] * $item['price'];
            }

            $unit = Unit::find($data['items'][0]['unit_id']);

            $transaction->update([
                'transaction_date' => $data['transaction_date'],
                'planned_usage_date' => $data['planned_usage_date'] ?? null,
                'job_level_id' => $data['job_level_id'],
                'job_position_id' => $data['job_position_id'],
                'program_id' => $data['program_id'],
                'purpose' => $data['purpose'],
                'estimated_amount' => $estimatedAmount,
                'urgency' => $data['urgency'],
                'unit_id' => $unit->id ?? 0,
                'unit_name' => $unit->name ?? '',
            ]);

            // Replace details
            $transaction->details()->delete();
            $this->createTransactionDetails($transaction->id, $data['items'], $data['urgency']);

            return [
                'success' => true,
                'message' => 'Transaction updated successfully',
                'data' => $transaction->load('details'),
            ];
        });
    }

    public function deleteTransaction(int $id): array
    {
        $transaction = $this->model->findOrFail($id);

        // Ownership check
        if ($transaction->user_id != Auth::id()) {
            return [
                'success' => false,
                'message' => 'Unauthorized access',
                'status_code' => 403,
            ];
        }

        // Status guard: only Submission (0)
        if ($transaction->status != 0) {
            return [
                'success' => false,
                'message' => 'Transaction cannot be deleted',
                'status_code' => 403,
            ];
        }

        return DB::transaction(function () use ($transaction) {
            $transaction->details()->delete();
            $transaction->delete();

            return [
                'success' => true,
                'message' => 'Transaction deleted successfully',
            ];
        });
    }

    /* ========================
        BUDGET / DROPDOWN
    ======================== */

    public function getBudgetInfo(int $budgetId): array
    {
        $budgetItem = WorkplanBudgetItem::with('budgetCodeRelation')->findOrFail($budgetId);

        $balanceResult = $this->budgetLedgerService->getBudgetBalance($budgetId);
        $currentBalance = $balanceResult['success']
            ? $balanceResult['data']['current_balance']
            : $budgetItem->total;

        return [
            'success' => true,
            'data' => [
                'budget_value' => $currentBalance,
                'budget_name' => $budgetItem->description,
                'budget_code' => $budgetItem->budget_code,
            ],
        ];
    }

    public function getJobPositionsByLevel(int $jobLevelId): array
    {
        $jobPositions = JobPosition::where('job_level_id', $jobLevelId)
            ->orderBy('job_position_name')
            ->get(['id', 'job_position_name']);

        return [
            'success' => true,
            'data' => $jobPositions,
        ];
    }

    public function getProgramsByJobLevel(int $jobLevelId): array
    {
        $jobLevel = JobLevel::find($jobLevelId);

        if (! $jobLevel) {
            return [
                'success' => false,
                'message' => 'Job level not found',
                'status_code' => 404,
            ];
        }

        $jobLevelName = strtolower($jobLevel->job_level_name);

        $kpiType = null;
        if (str_contains($jobLevelName, 'section') || str_contains($jobLevelName, 'staff')) {
            $kpiType = 'section';
        } elseif (str_contains($jobLevelName, 'department') || str_contains($jobLevelName, 'manager') || str_contains($jobLevelName, 'head')) {
            $kpiType = 'department';
        }

        $user = Auth::user();
        $isAdmin = $this->userRoleService->isAdmin($user);
        $divisionIds = $isAdmin ? [] : $this->userRoleService->getDivisionIds($user);

        $currentYear = (int) now()->year;

        $query = KPIWorkPlan::with(['KPIDepartment.department', 'kpiSection.section'])
            ->where('year', $currentYear)
            ->orderBy('year', 'desc')
            ->orderBy('activity');

        if (! $isAdmin) {
            // Non-admin wajib dibatasi ke divisi login (fail closed via scope).
            $query->whereDivisionIn($divisionIds);
        }

        if ($kpiType) {
            $query->where('kpi_type', $kpiType);
        }

        $workplans = $query->get();

        $formattedWorkplans = $workplans->map(function ($workplan) {
            $label = $workplan->activity . ' (' . $workplan->year . ')';

            if ($workplan->kpi_type === 'department' && $workplan->KPIDepartment) {
                $label .= ' - ' . ($workplan->KPIDepartment->department->department_name ?? '');
            } elseif ($workplan->kpi_type === 'section' && $workplan->kpiSection) {
                $label .= ' - ' . ($workplan->kpiSection->section->section_name ?? '');
            }

            return [
                'id' => $workplan->id,
                'activity' => $workplan->activity,
                'year' => $workplan->year,
                'kpi_type' => $workplan->kpi_type,
                'label' => $label,
            ];
        });

        return [
            'success' => true,
            'data' => $formattedWorkplans,
            'kpi_type' => $kpiType,
        ];
    }

    public function getBudgetItemsByProgram(int $programId): array
    {
        $budgetItems = WorkplanBudgetItem::where('kpi_workplan_id', $programId)
            ->approved()
            ->with(['budgetCodeRelation', 'category'])
            ->orderBy('description')
            ->get();

        $formattedItems = $budgetItems->map(function ($item) {
            $balanceResult = $this->budgetLedgerService->getBudgetBalance($item->id);
            $currentBalance = $balanceResult['success']
                ? $balanceResult['data']['current_balance']
                : $item->total;

            return [
                'id' => $item->id,
                'description' => $item->description,
                'budget_code' => $item->budget_code,
                'category_name' => $item->category->category_name ?? '',
                'total' => $currentBalance,
                'label' => $item->description . ' (' . $item->budget_code . ')',
                'unit_id' => $item->unit_id,
                'unit_name' => $item->unit_name,
            ];
        });

        return [
            'success' => true,
            'data' => $formattedItems,
        ];
    }

    public function validateBudgetItems(array $items): array
    {
        $budgetErrors = [];

        foreach ($items as $index => $item) {
            $budgetItem = WorkplanBudgetItem::find($item['budget_id']);

            if ($budgetItem) {
                $totalItemCost = $item['quantity'] * $item['price'];
                
                // Use current balance from BudgetLedgerService
                $balanceResult = $this->budgetLedgerService->getBudgetBalance($budgetItem->id);
                $currentBalance = $balanceResult['success']
                    ? $balanceResult['data']['current_balance']
                    : $budgetItem->total;

                if ($totalItemCost > $currentBalance) {
                    $budgetErrors[] = [
                        'item' => $item['goods_service_name'] ?? 'Item ' . ($index + 1),
                        'total' => 'Rp ' . number_format($totalItemCost, 0, ',', '.'),
                        'budget' => 'Rp ' . number_format($currentBalance, 0, ',', '.'),
                        'budget_code' => $budgetItem->budget_code ?? 'Unknown',
                    ];
                }
            }
        }

        return $budgetErrors;
    }

    /* ========================
        APPROVAL ACTIONS
    ======================== */

    public function processApprovalAction(int $transactionId, string $action, ?string $comments = null): array
    {
        $user = Auth::user();
        $employment = $user->employment;

        if (! $employment) {
            return [
                'success' => false,
                'message' => 'Employment data not found',
                'status_code' => 404,
            ];
        }

        $approvalRequest = ApprovalRequest::where('reference_id', $transactionId)
            ->whereHas('module', fn($q) => $q->where('table_name', 'transactions'))
            ->where('status', 'pending')
            ->first();

        if (! $approvalRequest) {
            return [
                'success' => false,
                'message' => 'Approval system not configured for this transaction',
                'status_code' => 404,
            ];
        }

        $pendingDetail = ApprovalRequestDetail::where('request_id', $approvalRequest->id)
            ->where('employment_id', $employment->id)
            ->where('status', 'pending')
            ->first();

        if (! $pendingDetail) {
            $label = $action === 'approve' ? 'approval' : 'reject';

            return [
                'success' => false,
                'message' => "Anda tidak memiliki akses untuk {$label} ini atau approval sudah diproses.",
                'status_code' => 404,
            ];
        }

        // Check sequence
        $nextPending = ApprovalRequestDetail::where('request_id', $approvalRequest->id)
            ->where('status', 'pending')
            ->orderBy('level_sequence')
            ->first();

        if ($nextPending && $nextPending->id !== $pendingDetail->id) {
            return [
                'success' => false,
                'message' => 'Menunggu approval dari level sebelumnya.',
                'status_code' => 400,
            ];
        }

        return $this->approvalTransactionService->processApproval(
            $pendingDetail->id,
            $action,
            $employment->id,
            $comments
        );
    }

    public function cancelApprovalRequest(int $transactionId): array
    {
        $transaction = $this->model->findOrFail($transactionId);

        if ($transaction->user_id != Auth::id()) {
            return [
                'success' => false,
                'message' => 'Unauthorized access',
                'status_code' => 403,
            ];
        }

        return $this->approvalTransactionService->cancelApproval($transactionId);
    }

    public function resubmitForApproval(int $transactionId): array
    {
        $transaction = $this->model->findOrFail($transactionId);

        if ($transaction->user_id != Auth::id()) {
            return [
                'success' => false,
                'message' => 'Unauthorized access',
                'status_code' => 403,
            ];
        }

        if (! in_array($transaction->status, [Transaction::STATUS_PENDING, Transaction::STATUS_CANCELLED])) {
            return [
                'success' => false,
                'message' => 'Transaction cannot be resubmitted in current status',
                'status_code' => 400,
            ];
        }

        return $this->approvalTransactionService->submitForApproval($transactionId);
    }

    /* ========================
        BADGE / PDF
    ======================== */

    public function getApprovalBadgeHtml(int $transactionId): array
    {
        $this->model->findOrFail($transactionId);

        $timelineResult = $this->approvalTransactionService->getApprovalTimeline($transactionId);

        if (! $timelineResult['success'] || empty($timelineResult['data'])) {
            return [
                'success' => false,
                'message' => 'No approval timeline found for this transaction',
                'status_code' => 404,
            ];
        }

        $data = [];
        foreach ($timelineResult['data'] as $item) {
            $iconClass = $item['badge_class'] ?? 'bg-secondary';
            $badgeClass = $item['badge_class'] ?? 'bg-secondary';
            $isPending = ($item['status'] ?? '') === 'pending';

            if ($isPending) {
                $data[] = '<div class="tt-item">
                    <div class="tt-icon bg-light">
                        <span class="tt-dot"></span>
                    </div>
                    <div class="tt-content">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="fw-semibold"></div>
                            <span class="badge rounded-pill bg-light text-muted">Pending</span>
                        </div>
                        <div class="small mt-1 text-muted">' . htmlspecialchars($item['label'] ?? '') . ' by <span class="fw-semibold">' . htmlspecialchars($item['approver_name'] ?? 'Waiting') . '</span></div>
                    </div>
                </div>';
            } else {
                $data[] = '<div class="tt-item">
                    <div class="tt-icon ' . $iconClass . '">
                        <span class="tt-dot"></span>
                    </div>
                    <div class="tt-content">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="fw-semibold">' . htmlspecialchars($item['date'] ?? '') . '</div>
                            <span class="badge rounded-pill ' . $badgeClass . ' text-white">' . htmlspecialchars($item['label'] ?? '') . '</span>
                        </div>
                        <div class="small mt-1">' . htmlspecialchars($item['description'] ?? '') . '</div>
                    </div>
                </div>';
            }
        }

        return [
            'success' => true,
            'data' => implode('', $data),
        ];
    }

    public function generatePdfData(int $id): array
    {
        $transaction = $this->model->where('id', $id)->get();
        $transactionApproval = $this->approvalTransactionService->getApprovalTimeline($id);

        $qrSubmission = null;
        $qrApprovals = [];

        if ($transactionApproval['success'] && !empty($transactionApproval['data'])) {

            foreach ($transactionApproval['data'] as $item) {

                /**
                 * 1️⃣ QR UNTUK SUBMISSION
                 */
                if ($item['type'] === 'submission') {

                    $qrText = 'Ref Number : ' . $item['reference_number'] . ' | ' .
                        'Proposed by | ' .
                        $item['date'];

                    $qrSubmission = $this->generateQR($qrText);
                }

                /**
                 * 2️⃣ QR UNTUK APPROVAL (HANYA YANG APPROVED)
                 */
                if ($item['type'] === 'approval' && $item['status'] === 'approved') {

                    $qrText = 'Ref Number : ' . $item['reference_number'] . ' | ' .
                        'APPROVED | ' .
                        $item['approver_name'] . ' | ' .
                        $item['label'] . ' | ' .
                        $item['date'];

                    $qrApprovals[] = [
                        'approver_name' => $item['approver_name'],
                        'level' => $item['level'],
                        'phase' => $item['phase'],
                        'date' => $item['date'],
                        'qr' => $this->generateQR($qrText)
                    ];
                }
            }
        }

        return [
            'transaction' => $transaction,
            'transactionApproval' => $transactionApproval,
            'qrSubmission' => $qrSubmission,
            'qrApprovals' => $qrApprovals,
        ];
    }

    public function generateQR(string $qrText): string
    {
        $qrCode = new QrCode(
            data: $qrText,
            encoding: new Encoding('UTF-8'),
            size: 300,
            margin: 10
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return base64_encode($result->getString());
    }

    /* ========================
        PRIVATE HELPERS
    ======================== */

    public function getDueDatePageData(): array
    {
        $userId = Auth::id();
        $employee = Auth::user();
        $employment = $employee->employment;

        // Current user's overdue transactions 
        // 1. Submissions (Submission, Progress, Approved) past H+2
        // 2. LPJ (Paid but no LPJ) past H+7
        $dueDateCount = $this->model->where('user_id', $userId)
            ->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->whereIn('status', [
                        Transaction::STATUS_SUBMISSION,
                        Transaction::STATUS_PROGRESS,
                        Transaction::STATUS_APPROVED
                    ])
                        ->where('transaction_date', '<=', now()->subDays(2)->toDateString());
                })
                    ->orWhere(function ($sq) {
                        $sq->where('status', Transaction::STATUS_PAID)
                            ->where('transaction_date', '<=', now()->subDays(7)->toDateString())
                            ->whereDoesntHave('lpjSubmission');
                    });
            })
            ->count();

        $years = $this->model->selectRaw('YEAR(transaction_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $jobLevels = JobLevel::all();
        $jobPositions = JobPosition::all();
        $workplans = KPIWorkPlan::with(['KPIDepartment', 'kpiSection'])->get();
        $budgetCodes = WorkplanBudgetItem::with('budgetCodeRelation')->get();
        $units = Unit::all();

        return [
            'dueDateCount' => $dueDateCount,
            'years' => $years,
            'jobLevels' => $jobLevels,
            'jobPositions' => $jobPositions,
            'workplans' => $workplans,
            'budgetCodes' => $budgetCodes,
            'units' => $units,
            'employment' => $employment,
        ];
    }

    public function getDueDateTransactions(array $filters = []): array
    {
        $userId = Auth::id();
        $user = Auth::user();
        $employment = $user->employment;
        $employmentId = $employment ? $employment->id : null;

        $query = $this->model->query()
            ->where('user_id', $userId)
            ->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->whereIn('status', [
                        Transaction::STATUS_SUBMISSION,
                        Transaction::STATUS_PROGRESS,
                        Transaction::STATUS_APPROVED
                    ])
                        ->where('transaction_date', '<=', now()->subDays(2)->toDateString());
                })
                    ->orWhere(function ($sq) {
                        $sq->where('status', Transaction::STATUS_PAID)
                            ->where('transaction_date', '<=', now()->subDays(7)->toDateString())
                            ->whereDoesntHave('lpjSubmission');
                    });
            })
            ->with([
                'details',
                'approvalRequest.details' => fn($q) => $q->orderBy('level_sequence'),
                'lpjSubmission',
            ]);

        // Filter by year
        $year = $filters['year'] ?? null;
        if ($year && $year !== '' && $year !== 'all') {
            $query->whereYear('transaction_date', $year);
        }

        $perPage = $filters['per_page'] ?? 10;
        $transactions = $query->orderBy('transaction_date', 'asc')->paginate($perPage);

        // Add approval flags to each transaction
        $transactions->getCollection()->transform(function ($transaction) use ($employmentId) {
            $transaction->can_approve = false;
            $transaction->pending_approval = null;
            $transaction->can_edit = false;
            $transaction->can_submit_lpj = $transaction->canSubmitLpj();

            return $transaction;
        });

        return [
            'success' => true,
            'data' => $transactions,
        ];
    }

    /**
     * Check if any approver has already approved the transaction.
     */
    private function hasAnyApproverApproved(Transaction $transaction): bool
    {
        if ($transaction->approvalRequest && $transaction->approvalRequest->details) {
            return $transaction->approvalRequest->details
                ->where('status', 'approved')
                ->isNotEmpty();
        }

        return false;
    }

    /**
     * Create transaction detail records for given items.
     */
    private function createTransactionDetails(int $transactionId, array $items, string $urgency): void
    {
        foreach ($items as $item) {
            $budgetItem = WorkplanBudgetItem::with('budgetCodeRelation')->find($item['budget_id']);
            $unit = Unit::find($item['unit_id']);
            $total = $item['quantity'] * $item['price'];

            TransactionDetail::create([
                'transaction_id' => $transactionId,
                'budget_id' => $budgetItem->id,
                'budget_name' => $budgetItem->description ?? '',
                'goods_service_name' => $item['goods_service_name'],
                'balance' => $budgetItem->total ?? 0,
                'estimated_price' => $item['price'],
                'estimated_quantity' => $item['quantity'],
                'estimated_total' => $total,
                'fix_price' => 0,
                'fix_quantity' => 0,
                'fix_total' => 0,
                'unit_id' => $unit->id,
                'unit_name' => $unit->unit ?? $unit->name ?? '',
                'remark' => $item['remark'] ?? '',
                'urgency' => $urgency,
                'status' => 0,
            ]);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new SubmissionTemplateExport(), 'submission_template.xlsx');
    }

    public function importTransactions($file): array
    {
        try {
            $collections = Excel::toCollection(new SubmissionImport(), $file);
            $rows = $collections->first();

            if ($rows->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'The uploaded file is empty.',
                ];
            }

            $user = Auth::user();
            $employment = $user->employment;

            if (!$employment) {
                return [
                    'success' => false,
                    'message' => 'User employment data not found. Cannot proceed with import.',
                ];
            }

            // Group rows by Ref No, Date, Program, Purpose, Urgency
            $groupedTransactions = [];
            foreach ($rows as $index => $row) {
                // Skip if essential fields are missing
                if (empty($row['program_name']) || empty($row['budget_code'])) {
                    continue;
                }

                $refNo = $row['ref_no'] ?? 'default';
                $progName = trim($row['program_name']);
                $purpose = trim($row['purpose'] ?? 'Imported Submission');
                $urgency = trim($row['urgency'] ?? 'Low');
                $transDate = !empty($row['transaction_date_yyyy_mm_dd']) ? $row['transaction_date_yyyy_mm_dd'] : date('Y-m-d');
                $plannedDate = !empty($row['planned_usage_date_yyyy_mm_dd']) ? $row['planned_usage_date_yyyy_mm_dd'] : null;

                $groupKey = "{$refNo}_{$progName}_{$purpose}_{$urgency}_{$transDate}";

                if (!isset($groupedTransactions[$groupKey])) {
                    $groupedTransactions[$groupKey] = [
                        'transaction_date' => $transDate,
                        'planned_usage_date' => $plannedDate,
                        'program_name' => $progName,
                        'purpose' => $purpose,
                        'urgency' => $urgency,
                        'items' => []
                    ];
                }

                $groupedTransactions[$groupKey]['items'][] = [
                    'goods_service_name' => $row['item_name'] ?? 'Item ' . (count($groupedTransactions[$groupKey]['items']) + 1),
                    'budget_code' => trim($row['budget_code']),
                    'unit_name' => trim($row['unit'] ?? 'Pcs'),
                    'quantity' => (float)($row['quantity'] ?? 1),
                    'price' => (float)($row['price'] ?? 0),
                    'remark' => $row['remark'] ?? '',
                ];
            }

            if (empty($groupedTransactions)) {
                return [
                    'success' => false,
                    'message' => 'No valid transaction data found in the file.',
                ];
            }

            $results = [
                'created' => 0,
                'errors' => [],
            ];

            foreach ($groupedTransactions as $key => $transData) {
                try {
                    $result = DB::transaction(function () use ($transData, $user, $employment) {
                        // 1. Find Program (KPIWorkPlan)
                        $program = KPIWorkPlan::where('activity', $transData['program_name'])
                            ->where('year', date('Y', strtotime($transData['transaction_date'])))
                            ->first();

                        if (!$program) {
                            throw new \Exception("Program '{$transData['program_name']}' for year " . date('Y', strtotime($transData['transaction_date'])) . " not found.");
                        }

                        // 2. Prepare items with IDs
                        $preparedItems = [];
                        foreach ($transData['items'] as $item) {
                            // Find Budget Item
                            $budgetItem = WorkplanBudgetItem::where('budget_code', $item['budget_code'])
                                ->where('kpi_workplan_id', $program->id)
                                ->approved() // Only approved budget items
                                ->first();

                            if (!$budgetItem) {
                                throw new \Exception("Budget code '{$item['budget_code']}' not found (or not approved) in program '{$transData['program_name']}'.");
                            }

                            // Find Unit
                            $unit = Unit::where('unit', 'like', $item['unit_name'])->first();
                            if (!$unit) {
                                // Default to first unit if not found or throw error? Let's throw error for data integrity.
                                throw new \Exception("Unit '{$item['unit_name']}' not found.");
                            }

                            $preparedItems[] = [
                                'budget_id' => $budgetItem->id,
                                'unit_id' => $unit->id,
                                'goods_service_name' => $item['goods_service_name'],
                                'quantity' => $item['quantity'],
                                'price' => $item['price'],
                                'remark' => $item['remark'],
                            ];
                        }

                        // 3. Create Transaction using existing service logic with approval flow
                        $transactionData = [
                            'transaction_date' => $transData['transaction_date'],
                            'planned_usage_date' => $transData['planned_usage_date'],
                            'job_level_id' => $employment->job_level_id,
                            'job_position_id' => $employment->job_position_id,
                            'program_id' => $program->id,
                            'purpose' => $transData['purpose'],
                            'urgency' => $transData['urgency'],
                            'items' => $preparedItems,
                        ];

                        $createResult = $this->createTransaction($transactionData);

                        if (!$createResult['success']) {
                            $msg = $createResult['message'];
                            if (!empty($createResult['budget_errors'])) {
                                $budgetErrStr = implode(', ', array_map(function($err) {
                                    return "{$err['item']} (Maks: {$err['budget']})";
                                }, $createResult['budget_errors']));
                                $msg .= ' ' . $budgetErrStr;
                            }
                            throw new \Exception($msg);
                        }

                        return true;
                    });

                    if ($result) {
                        $results['created']++;
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = "Row group '{$transData['program_name']}': " . $e->getMessage();
                }
            }

            return [
                'success' => $results['created'] > 0,
                'message' => "Berhasil mengimpor {$results['created']} transaksi dan mengajukannya ke dalam proses Approval." . (count($results['errors']) > 0 ? " Beberapa transaksi gagal diproses." : ""),
                'errors' => $results['errors'],
                'data' => $results,
            ];
        } catch (\Exception $e) {
            Log::error('Import Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error processing Excel file: ' . $e->getMessage(),
            ];
        }
    }

    /* ========================
        MACFRAME GA IMPORT
    ======================== */

    /**
     * Phase 1 – Parse only, no DB write.
     *
     * MacframeGA Excel structure:
     *   Row 1 : Master header  (Date, Own bank account, …)
     *   Row 2 : Master data values
     *   Row 3 : Detail header  (Detail type, Goods/Charges, D Descript, …)
     *   Row 4+ : Detail rows
     */
    public function parseMacframeFile($file): array
    {
        try {
            $collections = Excel::toCollection(new MacframeImport(), $file);
            $rows        = $collections->first();

            if ($rows === null || $rows->isEmpty()) {
                return ['success' => false, 'message' => 'File kosong atau tidak dapat dibaca.'];
            }

            $rowArray = $rows->toArray();

            if (count($rowArray) < 4) {
                return [
                    'success' => false,
                    'message' => 'Format file tidak sesuai MacframeGA. Dibutuhkan minimal 4 baris.',
                ];
            }

            // Row index 1 (second row) = master data
            $masterRow = $rowArray[1];

            // Column 0 = Date field in master row
            $rawDate = $masterRow[0] ?? null;
            $transactionDate = date('Y-m-d');

            if (!empty($rawDate)) {
                if (is_numeric($rawDate)) {
                    try {
                        $dt = ExcelDate::excelToDateTimeObject((float) $rawDate);
                        $transactionDate = $dt->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $transactionDate = date('Y-m-d');
                    }
                } else {
                    $parsed = strtotime((string) $rawDate);
                    $transactionDate = $parsed !== false ? date('Y-m-d', $parsed) : date('Y-m-d');
                }
            }

            // Detail header is row 3 (index 2), detail data starts from row 4 (index 3)
            $unitMap = Unit::all()->keyBy(fn ($u) => strtolower(trim($u->unit ?? $u->name ?? '')));

            $parsedItems = [];
            $purposes    = [];
            $urgencies   = [];

            for ($i = 3; $i < count($rowArray); $i++) {
                $row = $rowArray[$i];

                $cellValues = array_filter(array_map('trim', array_map('strval', $row)));
                if (empty($cellValues)) {
                    continue;
                }

                // Column mapping (0-based):
                // 1 = Goods/Charges, 2 = D Descript, 3 = D Descript(contents), 5 = Unit, 6 = Qty, 7 = Price
                $goodsCharges  = trim((string) ($row[1] ?? ''));
                $dDescript     = trim((string) ($row[2] ?? ''));
                $dDescContents = trim((string) ($row[3] ?? ''));
                $unitName      = trim((string) ($row[5] ?? ''));
                $qty           = (float) ($row[6] ?? 0);
                $price         = (float) ($row[7] ?? 0);

                if (empty($goodsCharges)) {
                    continue;
                }

                $unitKey = strtolower($unitName);
                $unitObj = $unitMap->get($unitKey);
                $unitId  = $unitObj?->id ?? null;

                if (!empty($dDescript)) {
                    $purposes[] = $dDescript;
                }
                if (!empty($dDescContents)) {
                    $urgencies[] = $dDescContents;
                }

                $parsedItems[] = [
                    'goods_service_name' => $goodsCharges,
                    'purpose'            => $dDescript,
                    'urgency'            => $dDescContents,
                    'unit_name'          => $unitName,
                    'unit_id'            => $unitId,
                    'quantity'           => $qty,
                    'price'              => $price,
                    'total'              => round($qty * $price, 2),
                    'unit_unresolved'    => ($unitId === null && !empty($unitName)),
                ];
            }

            if (empty($parsedItems)) {
                return [
                    'success' => false,
                    'message' => 'Tidak ditemukan baris detail yang valid di file MacframeGA.',
                ];
            }

            $aggregatePurpose = !empty($purposes) ? implode('; ', array_unique($purposes)) : 'Imported from MacframeGA';
            $aggregateUrgency = !empty($urgencies) ? $urgencies[0] : 'low';

            return [
                'success'          => true,
                'message'          => 'File berhasil diparsing. Silakan pilih Program ID dan konfirmasi.',
                'transaction_date' => $transactionDate,
                'purpose'          => $aggregatePurpose,
                'urgency'          => $aggregateUrgency,
                'data'             => $parsedItems,
            ];

        } catch (\Throwable $e) {
            Log::error('MacframeGA parse error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'success' => false,
                'message' => 'Gagal memproses file: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Phase 2 – Commit parsed MacframeGA items to DB inside DB::transaction closure.
     */
    public function commitMacframeTransactions(
        array  $parsedRows,
        int    $programId,
        string $transactionDate,
        string $purpose,
        string $urgency
    ): array {
        $user       = Auth::user();
        $employment = $user->employment;

        if (!$employment) {
            return ['success' => false, 'message' => 'Data employment user tidak ditemukan.', 'status_code' => 422];
        }

        $program = KPIWorkPlan::find($programId);
        if (!$program) {
            return ['success' => false, 'message' => "Program ID {$programId} tidak ditemukan.", 'status_code' => 422];
        }

        return DB::transaction(function () use ($parsedRows, $programId, $transactionDate, $purpose, $urgency, $user, $employment) {
            $unitMap         = Unit::all()->keyBy(fn ($u) => strtolower(trim($u->unit ?? $u->name ?? '')));
            $preparedItems   = [];
            $estimatedAmount = 0;
            $firstUnit       = null;

            foreach ($parsedRows as $row) {
                $unitName = trim((string) ($row['unit_name'] ?? ''));
                $unitKey  = strtolower($unitName);
                $unitObj  = $unitMap->get($unitKey);

                if (!$unitObj && !empty($unitName)) {
                    throw new \App\Exceptions\DomainException(
                        "Unit '{$unitName}' tidak ditemukan. Tambahkan unit terlebih dahulu."
                    );
                }

                if ($firstUnit === null && $unitObj) {
                    $firstUnit = $unitObj;
                }

                $qty   = (float) ($row['quantity'] ?? 0);
                $price = (float) ($row['price'] ?? 0);
                $estimatedAmount += round($qty * $price, 2);

                $preparedItems[] = [
                    'goods_service_name' => $row['goods_service_name'],
                    'unit_id'            => $unitObj?->id ?? 0,
                    'quantity'           => $qty,
                    'price'              => $price,
                    'budget_id'          => $row['budget_id'] ?? 0,
                ];
            }

            // Validate budget sufficiency before proceeding
            $budgetErrors = $this->validateBudgetItems($preparedItems);
            if (!empty($budgetErrors)) {
                return [
                    'success' => false,
                    'message' => 'Saldo budget tidak mencukupi untuk beberapa item.',
                    'budget_errors' => $budgetErrors,
                    'status_code' => 422,
                ];
            }

            $transaction = $this->model->create([
                'transaction_date'   => $transactionDate,
                'planned_usage_date' => null,
                'user_id'            => $user->id,
                'user_name'          => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                'unit_id'            => $firstUnit?->id ?? 0,
                'unit_name'          => $firstUnit?->unit ?? $firstUnit?->name ?? '',
                'job_level_id'       => $employment->job_level_id,
                'job_position_id'    => $employment->job_position_id,
                'program_id'         => $programId,
                'purpose'            => $purpose,
                'estimated_amount'   => $estimatedAmount,
                'actual_amount'      => 0,
                'urgency'            => $urgency,
                'status'             => Transaction::STATUS_PENDING,
            ]);

            foreach ($preparedItems as $item) {
                $unit = Unit::find($item['unit_id']);
                $budgetItem = WorkplanBudgetItem::find($item['budget_id']);

                TransactionDetail::create([
                    'transaction_id'     => $transaction->id,
                    'budget_id'          => $item['budget_id'],
                    'budget_name'        => $budgetItem->description ?? '',
                    'goods_service_name' => $item['goods_service_name'],
                    'balance'            => $budgetItem->total ?? 0,
                    'estimated_price'    => $item['price'],
                    'estimated_quantity' => $item['quantity'],
                    'estimated_total'    => round($item['quantity'] * $item['price'], 2),
                    'fix_price'          => 0,
                    'fix_quantity'       => 0,
                    'fix_total'          => 0,
                    'unit_id'            => $item['unit_id'],
                    'unit_name'          => $unit?->unit ?? $unit?->name ?? '',
                    'remark'             => 'Imported from MacframeGA',
                    'urgency'            => $urgency,
                    'status'             => 0,
                ]);
            }

            $approvalResult = $this->approvalTransactionService->submitForApproval($transaction->id);

            if (!$approvalResult['success']) {
                Log::warning('MacframeGA: approval submit fail – ' . $approvalResult['message']);
                throw new \App\Exceptions\DomainException(
                    'Gagal memulai alur approval: ' . $approvalResult['message']
                );
            }

            return [
                'success' => true,
                'message' => 'Import MacframeGA berhasil. Transaksi telah diajukan ke alur approval.',
                'data'    => $transaction->load(['details', 'approvalRequest.details']),
            ];
        });
    }

    /**
     * Webhook to update transaction status from PAID (3) to COMPLETED (4).
     */
    public function updateStatusToCompleted(int $id): array
    {
        try {
            $transaction = $this->model->find($id);

            if (!$transaction) {
                return ['success' => false, 'message' => 'Transaction not found'];
            }

            if ($transaction->status !== Transaction::STATUS_PAID) {
                return [
                    'success' => false, 
                    'message' => 'Transaction status must be PAID (3) to be marked as COMPLETED. Current status: ' . $transaction->status
                ];
            }

            $transaction->update([
                'status' => Transaction::STATUS_COMPLETED
            ]);

            return [
                'success' => true,
                'message' => 'Transaction status updated to COMPLETED successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Error updating status to completed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating transaction status: ' . $e->getMessage()];
        }
    }
}
