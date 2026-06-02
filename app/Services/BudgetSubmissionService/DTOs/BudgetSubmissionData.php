<?php

namespace App\Services\BudgetSubmissionService\DTOs;

readonly class BudgetSubmissionData
{
    public function __construct(
        public int $division_id,
        public string $submission_date,
        public string $type,
        public int $work_plan_id,
        public int|string $budget_account_id,
        public float $estimation_amount,
        public ?string $description = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            division_id: (int) $data['division_id'],
            submission_date: $data['submission_date'],
            type: $data['type'],
            work_plan_id: (int) $data['work_plan_id'],
            budget_account_id: $data['budget_account_id'],
            estimation_amount: (float) $data['estimation_amount'],
            description: $data['description'] ?? null
        );
    }
}
