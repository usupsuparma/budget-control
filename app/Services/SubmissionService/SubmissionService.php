<?php

namespace App\Services\SubmissionService;

use Illuminate\Http\Request;

/**
 * Service interface for Submission (Transaction) operations.
 *
 * Handles all business logic for the submission module:
 * CRUD transactions, budget validation, dropdown data, PDF generation, and badge timeline.
 */
interface SubmissionService
{
    /**
     * Get summary counts and dropdown data for the user submission page.
     *
     * @return array Page data including summary counts, years, statuses, and dropdown options
     */
    public function getUserPageData(): array;

    /**
     * Get filtered summary counts for the current user.
     *
     * @param  array  $filters  ['year' => string|null]
     * @return array ['success' => bool, 'data' => array]
     */
    public function getSummary(array $filters = []): array;

    /**
     * Get paginated transactions for the current user with approval flags.
     *
     * @param  array  $filters  ['year', 'status', 'per_page']
     * @return array ['success' => bool, 'data' => mixed]
     */
    public function getTransactions(array $filters = []): array;

    /**
     * Get full transaction detail with authorization and approval checks.
     *
     * @param  int  $id  Transaction ID
     * @return array ['success' => bool, 'data' => array]
     */
    public function getTransactionDetail(int $id): array;

    /**
     * Create a new transaction with details and submit for approval.
     *
     * @param  array  $data  Validated input from controller
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function createTransaction(array $data): array;

    /**
     * Update an existing transaction (only if no approver has approved yet).
     *
     * @param  array  $data  Validated input from controller
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function updateTransaction(int $id, array $data): array;

    /**
     * Delete a transaction (only if status is Submission/Pending).
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteTransaction(int $id): array;

    /**
     * Get budget balance info for a specific budget item.
     *
     * @param  int  $budgetId  WorkplanBudgetItem ID
     * @return array ['success' => bool, 'data' => array]
     */
    public function getBudgetInfo(int $budgetId): array;

    /**
     * Get job positions filtered by job level ID.
     *
     * @return array ['success' => bool, 'data' => array]
     */
    public function getJobPositionsByLevel(int $jobLevelId): array;

    /**
     * Get programs (KPI Workplans) filtered by job level.
     * Determines kpi_type based on job level name.
     *
     * @return array ['success' => bool, 'data' => array]
     */
    public function getProgramsByJobLevel(int $jobLevelId): array;

    /**
     * Get approved budget items filtered by workplan (program) ID.
     *
     * @param  int  $programId  KPIWorkPlan ID
     * @return array ['success' => bool, 'data' => array]
     */
    public function getBudgetItemsByProgram(int $programId): array;

    /**
     * Get approval timeline badge HTML for a transaction.
     *
     * @return array ['success' => bool, 'data' => string]
     */
    public function getApprovalBadgeHtml(int $transactionId): array;

    /**
     * Generate PDF data (transaction, approval timeline, QR codes) for a transaction.
     *
     * @param  int  $id  Transaction ID
     * @return array ['transaction', 'transactionApproval', 'qr*' codes]
     */
    public function generatePdfData(int $id): array;

    /**
     * Get summary counts and dropdown data for the approval page.
     *
     * @return array Page data for approval view
     */
    public function getApprovalPageData(): array;

    /**
     * Validate budget sufficiency for transaction items.
     * Checks that qty * price does not exceed budget value.
     *
     * @param  array  $items  Array of item data with budget_id, quantity, price
     * @return array Empty if valid, otherwise array of error details
     */
    public function validateBudgetItems(array $items): array;

    /**
     * Process approval action for a transaction.
     * Validates approver access, checks sequence, and delegates to ApprovalTransactionService.
     *
     * @param  string  $action  'approve' or 'reject'
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function processApprovalAction(int $transactionId, string $action, ?string $comments = null): array;

    /**
     * Cancel approval request for a transaction (owner only).
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function cancelApprovalRequest(int $transactionId): array;

    /**
     * Resubmit transaction for approval (owner only, valid statuses only).
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function resubmitForApproval(int $transactionId): array;

    /**
     * Get summary counts and dropdown data for the budget due date page.
     *
     * @return array Page data for due date view
     */
    public function getDueDatePageData(): array;

    /**
     * Get paginated transactions that are past their due date and haven't submitted LPJ.
     *
     * @param  array  $filters  ['year', 'per_page']
     * @return array ['success' => bool, 'data' => mixed]
     */
    public function getDueDateTransactions(array $filters = []): array;

    /**
     * Import transactions from Excel file.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function importTransactions($file): array;

    /**
     * Download Excel template for transaction import.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadTemplate();
}
