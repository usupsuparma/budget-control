<?php

namespace App\Services\LpjService;

/**
 * Service interface for LPJ (Laporan Pertanggungjawaban) operations.
 * 
 * Handles LPJ submission and approval workflow for transactions
 * after transaction approval is completed (APPROVED = 2).
 * Final LPJ approval will set the transaction status to PAID (3).
 */
interface LpjService
{
    /**
     * Submit LPJ for a transaction.
     * Creates approval chain based on LpjApprovalMaster.
     *
     * @param int $transactionId
     * @param array $data [submission_date, realization_date, items, proof_of_payment]
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function submitLpj(int $transactionId, array $data): array;

    /**
     * Get LPJ form data for a transaction.
     *
     * @param int $transactionId
     * @return array ['success' => bool, 'data' => array]
     */
    public function getLpjFormData(int $transactionId): array;

    /**
     * Process an LPJ approval action (approve/reject).
     *
     * @param int $lpjSubmissionId
     * @param string $action 'approve' or 'reject'
     * @param int $employmentId Employment ID of approver
     * @param string|null $notes Optional notes
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function processApproval(int $lpjSubmissionId, string $action, int $employmentId, ?string $notes = null): array;

    /**
     * Get LPJ details including approval status.
     *
     * @param int $lpjSubmissionId
     * @return array ['success' => bool, 'data' => array]
     */
    public function getLpjDetails(int $lpjSubmissionId): array;

    /**
     * Get pending LPJ approvals for a user (employment).
     *
     * @param int $employmentId
     * @param array $filters Optional filters
     * @return array ['success' => bool, 'data' => array, 'count' => int]
     */
    public function getPendingLpjApprovalsForUser(int $employmentId, array $filters = []): array;

    /**
     * Get LPJ approval counts for a user.
     *
     * @param int $employmentId
     * @param array $filters Optional filters
     * @return array ['success' => bool, 'data' => array]
     */
    public function getLpjApprovalCounts(int $employmentId, array $filters = []): array;

    /**
     * Update LPJ realization items.
     *
     * @param int $lpjSubmissionId
     * @param array $items
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateLpjItems(int $lpjSubmissionId, array $items): array;

    /**
     * Get LPJ by transaction ID.
     *
     * @param int $transactionId
     * @return array ['success' => bool, 'data' => mixed]
     */
    public function getLpjByTransactionId(int $transactionId): array;

    /**
     * Determine whether the given approver's action will fully close the LPJ.
     * Used to decide whether a FIS submission is required before approving.
     *
     * @param int $lpjSubmissionId
     * @param int $employmentId Employment ID of the prospective approver
     * @return bool
     */
    public function willBeFullyApprovedAfter(int $lpjSubmissionId, int $employmentId): bool;
}
