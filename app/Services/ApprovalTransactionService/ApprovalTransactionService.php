<?php

namespace App\Services\ApprovalTransactionService;

use App\Models\Transaction;

/**
 * Service interface for Transaction Approval operations.
 * 
 * This service handles dynamic approval workflow for transactions,
 * using the new approval system with ApprovalRequest and ApprovalRequestDetail.
 */
interface ApprovalTransactionService
{
    /**
     * Submit a transaction for approval.
     * Creates approval chain based on configured templates.
     *
     * @param int $transactionId
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function submitForApproval(int $transactionId): array;

    /**
     * Process an approval action (approve/reject).
     *
     * @param int $requestDetailId ApprovalRequestDetail ID
     * @param string $action 'approve' or 'reject'
     * @param int $approverId Employment ID of approver
     * @param string|null $comments Optional comments
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function processApproval(int $requestDetailId, string $action, int $approverId, ?string $comments = null): array;

    /**
     * Get approval status for a transaction.
     *
     * @param int $transactionId
     * @return array ['success' => bool, 'data' => array]
     */
    public function getApprovalStatus(int $transactionId): array;

    /**
     * Get pending approvals for a user (employment).
     *
     * @param int $employmentId
     * @return array ['success' => bool, 'data' => array, 'count' => int]
     */
    public function getPendingApprovalsForUser(int $employmentId): array;

    /**
     * Cancel an approval request for a transaction.
     *
     * @param int $transactionId
     * @return array ['success' => bool, 'message' => string]
     */
    public function cancelApproval(int $transactionId): array;

    /**
     * Get approval timeline for display.
     *
     * @param int $transactionId
     * @return array ['success' => bool, 'data' => array]
     */
    public function getApprovalTimeline(int $transactionId): array;
}