<?php

namespace App\Services\BudgetSubmissionService;

use App\Models\BudgetSubmission;
use App\Models\Division;
use App\Models\KPIWorkPlan;
use App\Models\WorkplanBudgetItem;
use App\Services\BudgetLedgerService\BudgetLedgerService;
use App\Services\BudgetSubmissionService\DTOs\BudgetSubmissionData;
use App\Services\UserRoleService\UserRoleService;
use App\Exceptions\DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BudgetSubmissionServiceImpl implements BudgetSubmissionService
{
    public function __construct(
        private readonly UserRoleService $userRoleService,
        private readonly BudgetLedgerService $budgetLedgerService
    ) {}

    public function getIndexData(mixed $user): array
    {
        $isAdmin = $this->userRoleService->isAdmin($user);
        $divisionIds = $isAdmin ? [] : $this->userRoleService->getDivisionIds($user);

        $budgetSubmissions = BudgetSubmission::with([
                'user',
                'division',
                'workPlan',
                'budgetAccount',
                'latestApprovalRequest',
            ])
            ->when(! $isAdmin, function ($query) use ($divisionIds) {
                $query->whereIn('division_id', $divisionIds);
            })
            ->orderBy('submission_date', 'desc')
            ->paginate(15);

        $divisions = Division::when(! $isAdmin, function ($query) use ($divisionIds) {
            $query->whereIn('id', $divisionIds);
        })->get();

        return compact('budgetSubmissions', 'divisions', 'user', 'isAdmin');
    }

    public function getAjaxData(mixed $user): Collection
    {
        return BudgetSubmission::with([
                'user',
                'division',
                'workPlan',
                'budgetAccount',
                'latestApprovalRequest',
            ])
            ->when(! $this->userRoleService->isAdmin($user), function ($query) use ($user) {
                $query->whereIn('division_id', $this->userRoleService->getDivisionIds($user));
            })
            ->orderBy('submission_date', 'desc')
            ->get();
    }

    public function show(int $id): array
    {
        $submission = BudgetSubmission::with(['user', 'division', 'workPlan', 'budgetAccount', 'sourceBudgetAccount'])
            ->findOrFail($id);

        return [
            'id' => $submission->id,
            'submission_date' => $submission->submission_date->format('d/m/Y'),
            'division' => $submission->division->name ?? '-',
            'type_label' => $submission->type_label,
            'work_plan' => $submission->workPlan->activity ?? '-',
            'budget_account' => $submission->budget_account_label,
            'source_budget_account' => $submission->source_budget_account_label,
            'description' => $submission->description ?? '-',
            'estimation_amount' => (int) $submission->estimation_amount,
            'status_label' => $submission->status_label,
            'status_color' => $submission->status_color,
            'created_by' => $submission->user?->first_name ?: ($submission->user?->full_name ?? '-'),
            'status' => (int) $submission->status,
        ];
    }

    public function getWorkPlansByDivision(?int $divisionId): array
    {
        if (! $divisionId) {
            return [];
        }

        $year = date('Y');

        return KPIWorkPlan::whereDivisionIn([$divisionId])
            ->where('year', $year)
            ->where('status', 'approved')
            ->select('id', 'activity', 'year')
            ->orderBy('activity')
            ->get()
            ->map(fn ($workPlan) => [
                'value' => $workPlan->id,
                'label' => '[' . $workPlan->year . '] ' . $workPlan->activity,
            ])
            ->toArray();
    }

    public function getBudgetItemsForDropdown(array $filters): array
    {
        $query = trim((string) ($filters['q'] ?? $filters['search'] ?? ''));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $limit = min(100, max(1, (int) ($filters['limit'] ?? 20)));
        $selectedId = $filters['id'] ?? null;
        $workPlanId = $filters['work_plan_id'] ?? null;

        if (! empty($selectedId)) {
            $selected = WorkplanBudgetItem::approved()->find($selectedId);

            if (! $selected) {
                return [
                    'success' => true,
                    'data' => [],
                    'has_more' => false,
                    'page' => 1,
                    'total' => 0,
                ];
            }

            $balanceResult = $this->budgetLedgerService->getBudgetBalance($selected->id);
            $balance = $balanceResult['success'] ? (float) $balanceResult['data']['current_balance'] : 0;

            return [
                'success' => true,
                'data' => [[
                    'value' => (string) $selected->id,
                    'label' => $this->formatBudgetItemDropdownLabel($selected, $balance),
                    'balance' => $balance,
                ]],
                'has_more' => false,
                'page' => 1,
                'total' => 1,
            ];
        }

        if (empty($workPlanId)) {
            return [
                'success' => true,
                'data' => [],
                'has_more' => false,
                'page' => $page,
                'total' => 0,
                'message' => 'Pilih workplan terlebih dahulu.',
            ];
        }

        $queryBuilder = WorkplanBudgetItem::query()
            ->approved()
            ->where('kpi_workplan_id', $workPlanId)
            ->select('id', 'kpi_workplan_id', 'budget_code', 'stock_code', 'description', 'total');

        if ($query !== '') {
            $queryBuilder->where(function ($builder) use ($query) {
                $builder->where('budget_code', 'like', '%' . $query . '%')
                    ->orWhere('stock_code', 'like', '%' . $query . '%')
                    ->orWhere('description', 'like', '%' . $query . '%');
            });
        }

        $queryBuilder->orderBy('budget_code')->orderBy('description');

        $total = $queryBuilder->count();
        $offset = ($page - 1) * $limit;
        $budgetItems = $queryBuilder->skip($offset)->take($limit)->get();
        $balances = $this->budgetLedgerService->getBulkBudgetBalances($budgetItems->pluck('id')->all());
        $balanceMap = $balances['success'] ? $balances['data'] : [];

        $data = $budgetItems->map(function (WorkplanBudgetItem $item) use ($balanceMap) {
            $balance = (float) ($balanceMap[$item->id]['current_balance'] ?? 0);

            return [
                'value' => (string) $item->id,
                'label' => $this->formatBudgetItemDropdownLabel($item, $balance),
                'balance' => $balance,
            ];
        })->values()->toArray();

        return [
            'success' => true,
            'data' => $data,
            'has_more' => (($offset + $limit) < $total),
            'page' => $page,
            'total' => $total,
            'query' => $query,
            'limit' => $limit,
        ];
    }

    public function store(BudgetSubmissionData $data, mixed $user): void
    {
        if (!$user) {
            throw new DomainException('User not authenticated. Please login again.');
        }

        $division = Division::find($data->division_id);
        if (!$division) {
            throw new DomainException('Division not found. Please select a valid division.');
        }

        $this->validateMovementData($data);

        DB::transaction(function () use ($data, $user, $division) {
            BudgetSubmission::create([
                'user_id' => $user->id,
                'division_id' => $data->division_id,
                'division_name' => $division->name,
                'work_plan_id' => $data->work_plan_id,
                'submission_date' => $data->submission_date,
                'type' => $data->type,
                'budget_account_id' => $data->budget_account_id,
                'source_budget_account_id' => $data->type === 'relocation' ? $data->source_budget_account_id : null,
                'estimation_amount' => $data->estimation_amount,
                'description' => $data->description,
                'status' => 0, // Pending
            ]);
        });
    }

    public function edit(int $id): array
    {
        $budgetSubmission = BudgetSubmission::with(['budgetAccount', 'sourceBudgetAccount'])->findOrFail($id);

        if (! $budgetSubmission->canBeEdited()) {
            throw new DomainException('Submission ini tidak dapat diedit karena status tidak dapat diubah atau sedang dalam proses approval.');
        }

        $budgetAccountText = null;
        if ($budgetSubmission->budgetAccount) {
            $budgetAccountText = $budgetSubmission->budget_account_label;
        }

        $sourceBudgetAccountText = null;
        if ($budgetSubmission->sourceBudgetAccount) {
            $sourceBudgetAccountText = $budgetSubmission->source_budget_account_label;
        }

        return [
            'id' => $budgetSubmission->id,
            'division_id' => $budgetSubmission->division_id,
            'submission_date' => $budgetSubmission->submission_date->format('Y-m-d'),
            'type' => $budgetSubmission->type,
            'work_plan_id' => $budgetSubmission->work_plan_id,
            'budget_account_id' => $budgetSubmission->budget_account_id,
            'budget_account_text' => $budgetAccountText,
            'source_budget_account_id' => $budgetSubmission->source_budget_account_id,
            'source_budget_account_text' => $sourceBudgetAccountText,
            'estimation_amount' => $budgetSubmission->estimation_amount,
            'description' => $budgetSubmission->description,
            'status' => $budgetSubmission->status,
        ];
    }

    public function update(int $id, BudgetSubmissionData $data): void
    {
        $budgetSubmission = BudgetSubmission::findOrFail($id);

        if (! $budgetSubmission->canBeEdited()) {
            throw new DomainException('Submission ini tidak dapat diubah karena status tidak dapat diubah atau sedang dalam proses approval.');
        }

        $division = Division::find($data->division_id);
        if (!$division) {
            throw new DomainException('Division not found. Please select a valid division.');
        }

        $this->validateMovementData($data);

        DB::transaction(function () use ($budgetSubmission, $data, $division) {
            $budgetSubmission->update([
                'division_id' => $data->division_id,
                'division_name' => $division->name,
                'work_plan_id' => $data->work_plan_id,
                'submission_date' => $data->submission_date,
                'type' => $data->type,
                'budget_account_id' => $data->budget_account_id,
                'source_budget_account_id' => $data->type === 'relocation' ? $data->source_budget_account_id : null,
                'estimation_amount' => $data->estimation_amount,
                'description' => $data->description,
            ]);
        });
    }

    public function destroy(int $id): void
    {
        $budgetSubmission = BudgetSubmission::findOrFail($id);

        if (! $budgetSubmission->canBeDeleted()) {
            throw new DomainException('Submission ini tidak dapat dihapus karena status tidak dapat diubah atau sedang dalam proses approval.');
        }

        DB::transaction(function () use ($budgetSubmission) {
            $budgetSubmission->delete();
        });
    }

    public function approve(int $id): void
    {
        $budgetSubmission = BudgetSubmission::findOrFail($id);

        if ($budgetSubmission->status != 0) {
            throw new DomainException('Only pending submissions can be approved. This submission has already been ' . 
                                     ($budgetSubmission->status == 1 ? 'approved' : 'rejected') . '.');
        }

        DB::transaction(function () use ($budgetSubmission) {
            $mutationResult = $this->budgetLedgerService->recordBudgetSubmissionMovement($budgetSubmission->id);

            if (! $mutationResult['success']) {
                throw new DomainException($mutationResult['message']);
            }

            $budgetSubmission->update(['status' => 1]);
        });
    }

    public function reject(int $id): void
    {
        $budgetSubmission = BudgetSubmission::findOrFail($id);

        if ($budgetSubmission->status != 0) {
            throw new DomainException('Only pending submissions can be rejected. This submission has already been ' . 
                                     ($budgetSubmission->status == 1 ? 'approved' : 'rejected') . '.');
        }

        DB::transaction(function () use ($budgetSubmission) {
            $budgetSubmission->update(['status' => 2]);
        });
    }

    private function validateMovementData(BudgetSubmissionData $data): void
    {
        $workPlan = KPIWorkPlan::where('id', $data->work_plan_id)
            ->where('status', 'approved')
            ->first();

        if (! $workPlan) {
            throw new DomainException('Workplan tidak ditemukan atau belum approved.');
        }

        $targetBudgetItem = WorkplanBudgetItem::approved()
            ->where('id', $data->budget_account_id)
            ->where('kpi_workplan_id', $data->work_plan_id)
            ->first();

        if (! $targetBudgetItem) {
            throw new DomainException('Budget item tujuan tidak ditemukan pada workplan yang dipilih atau belum approved.');
        }

        if ($data->type !== 'relocation') {
            return;
        }

        if (empty($data->source_budget_account_id)) {
            throw new DomainException('Budget item sumber wajib dipilih untuk relocation.');
        }

        if ((int) $data->source_budget_account_id === (int) $data->budget_account_id) {
            throw new DomainException('Budget item sumber dan tujuan relocation tidak boleh sama.');
        }

        $sourceBudgetItem = WorkplanBudgetItem::approved()
            ->where('id', $data->source_budget_account_id)
            ->where('kpi_workplan_id', $data->work_plan_id)
            ->first();

        if (! $sourceBudgetItem) {
            throw new DomainException('Budget item sumber tidak ditemukan pada workplan yang dipilih atau belum approved.');
        }

        $balanceResult = $this->budgetLedgerService->getBudgetBalance($sourceBudgetItem->id);
        if (! $balanceResult['success']) {
            throw new DomainException($balanceResult['message']);
        }

        $currentBalance = (float) $balanceResult['data']['current_balance'];
        if ($data->estimation_amount > $currentBalance) {
            throw new DomainException(
                'Saldo budget sumber tidak mencukupi. Saldo tersedia Rp '
                . number_format($currentBalance, 0, ',', '.')
                . ', nilai relocation Rp '
                . number_format($data->estimation_amount, 0, ',', '.')
                . '.'
            );
        }
    }

    private function formatBudgetItemDropdownLabel(WorkplanBudgetItem $budgetItem, float $balance): string
    {
        return BudgetSubmission::formatBudgetItemLabel($budgetItem)
            . ' | Saldo Rp '
            . number_format($balance, 0, ',', '.');
    }
}
