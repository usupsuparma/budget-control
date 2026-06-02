<?php

namespace App\Services\BudgetSubmissionService;

use App\Services\BudgetSubmissionService\DTOs\BudgetSubmissionData;

interface BudgetSubmissionService
{
    /**
     * Get data for the index view (paginated submissions, divisions, work plans).
     */
    public function getIndexData(mixed $user): array;

    /**
     * Get data for AJAX datatable.
     */
    public function getAjaxData(mixed $user): \Illuminate\Database\Eloquent\Collection;

    /**
     * Store a new budget submission.
     */
    public function store(BudgetSubmissionData $data, mixed $user): void;

    /**
     * Get data for edit modal.
     */
    public function edit(int $id): array;

    /**
     * Update an existing budget submission.
     */
    public function update(int $id, BudgetSubmissionData $data): void;

    /**
     * Delete a budget submission.
     */
    public function destroy(int $id): void;

    /**
     * Approve a budget submission.
     */
    public function approve(int $id): void;

    /**
     * Reject a budget submission.
     */
    public function reject(int $id): void;
}
