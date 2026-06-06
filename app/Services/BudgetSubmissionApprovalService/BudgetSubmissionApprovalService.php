<?php

namespace App\Services\BudgetSubmissionApprovalService;

interface BudgetSubmissionApprovalService
{
    /**
     * Submit a budget submission for dynamic approval.
     */
    public function submitForApproval(int $submissionId): array;

    /**
     * Process an approval action (approve/reject) by approval request detail id.
     */
    public function processApproval(
        int $detailId,
        string $action,
        int $approverId,
        ?string $comments = null,
        ?int $sourceBudgetAccountId = null,
        int|float|null $approvedAmount = null
    ): array;

    /**
     * Process multiple approval actions at once.
     */
    public function bulkProcessApproval(array $detailIds, string $action, int $approverId, ?string $comments = null): array;

    /**
     * Get approval timeline for one budget submission.
     */
    public function getApprovalTimelineForSubmission(int $submissionId): array;

    /**
     * Get pending approval tasks for user (employment id).
     */
    public function getPendingApprovalsForUser(int $employmentId): array;

    /**
     * Get approved items by user (employment id).
     */
    public function getApprovedApprovalsForUser(int $employmentId): array;
}
