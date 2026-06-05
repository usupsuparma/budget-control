<?php

namespace App\Services\BudgetSubmissionService;

use App\Models\BudgetSubmission;
use App\Models\Division;
use App\Models\KPIWorkPlan;
use App\Services\BudgetSubmissionService\DTOs\BudgetSubmissionData;
use App\Services\UserRoleService\UserRoleService;
use App\Exceptions\DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BudgetSubmissionServiceImpl implements BudgetSubmissionService
{
    public function __construct(
        private readonly UserRoleService $userRoleService
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
            ->orderBy('submission_date', 'desc')
            ->get();
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

        DB::transaction(function () use ($data, $user, $division) {
            BudgetSubmission::create([
                'user_id' => $user->id,
                'division_id' => $data->division_id,
                'division_name' => $division->name,
                'work_plan_id' => $data->work_plan_id,
                'submission_date' => $data->submission_date,
                'type' => $data->type,
                'budget_account_id' => $data->budget_account_id,
                'estimation_amount' => $data->estimation_amount,
                'description' => $data->description,
                'status' => 0, // Pending
            ]);
        });
    }

    public function edit(int $id): array
    {
        $budgetSubmission = BudgetSubmission::with('budgetAccount')->findOrFail($id);

        if (! $budgetSubmission->canBeEdited()) {
            throw new DomainException('Submission ini tidak dapat diedit karena status tidak dapat diubah atau sedang dalam proses approval.');
        }

        $budgetAccountText = null;
        if ($budgetSubmission->budgetAccount) {
            $budgetAccountText = $budgetSubmission->budgetAccount->stock_code . ' - ' . $budgetSubmission->budgetAccount->name;
        }

        return [
            'id' => $budgetSubmission->id,
            'division_id' => $budgetSubmission->division_id,
            'submission_date' => $budgetSubmission->submission_date->format('Y-m-d'),
            'type' => $budgetSubmission->type,
            'work_plan_id' => $budgetSubmission->work_plan_id,
            'budget_account_id' => $budgetSubmission->budget_account_id,
            'budget_account_text' => $budgetAccountText,
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

        DB::transaction(function () use ($budgetSubmission, $data, $division) {
            $budgetSubmission->update([
                'division_id' => $data->division_id,
                'division_name' => $division->name,
                'work_plan_id' => $data->work_plan_id,
                'submission_date' => $data->submission_date,
                'type' => $data->type,
                'budget_account_id' => $data->budget_account_id,
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
}
