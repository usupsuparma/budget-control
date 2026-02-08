<?php

namespace App\Services\BudgetLedgerService;

/**
 * Service interface for Budget Ledger (Buku Besar Anggaran) operations.
 * 
 * Manages budget mutations based on the immutable ledger pattern.
 * Pagu awal di workplan_budget_items bersifat statis - tidak boleh diubah.
 * Seluruh pergerakan uang dicatat sebagai mutasi di budget_mutations.
 */
interface BudgetLedgerService
{
    /**
     * Phase 1: Record Cash Advance debit mutations when transaction is fully approved.
     * Loops through transaction_details and creates DEBIT mutations.
     * 
     * @param int $transactionId
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function recordCashAdvanceMutations(int $transactionId): array;

    /**
     * Phase 3: Record LPJ Settlement mutations when LPJ is fully approved.
     * Calculates difference between estimated_total and fix_total per detail.
     * Creates CREDIT (refund) or DEBIT (reimburse) mutations.
     * 
     * @param int $transactionId
     * @param int $lpjSubmissionId
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function recordLpjSettlementMutations(int $transactionId, int $lpjSubmissionId): array;

    /**
     * Validate if budget has sufficient balance for a transaction before approval.
     * Checks each transaction_detail.estimated_total against the current_balance
     * of its linked workplan_budget_item.
     * 
     * @param int $transactionId
     * @return array ['success' => bool, 'message' => string, 'insufficient_items' => array]
     */
    public function validateBudgetSufficiency(int $transactionId): array;

    /**
     * Get current balance for a specific workplan_budget_item.
     * Formula: initial_budget - total_debit + total_credit
     * 
     * @param int $budgetItemId workplan_budget_items.id
     * @return array ['success' => bool, 'data' => ['initial_budget', 'total_debit', 'total_credit', 'current_balance']]
     */
    public function getBudgetBalance(int $budgetItemId): array;

    /**
     * Get mutation history for a specific budget item.
     * 
     * @param int $budgetItemId
     * @param array $filters Optional filters (category, date_from, date_to)
     * @return array ['success' => bool, 'data' => array]
     */
    public function getMutationHistory(int $budgetItemId, array $filters = []): array;

    /**
     * Get mutation summary for a transaction.
     * 
     * @param int $transactionId
     * @return array ['success' => bool, 'data' => array]
     */
    public function getTransactionMutations(int $transactionId): array;

    /**
     * Get balance overview for multiple budget items (e.g. for dashboard).
     * 
     * @param array $budgetItemIds Array of workplan_budget_items.id
     * @return array ['success' => bool, 'data' => array]
     */
    public function getBulkBudgetBalances(array $budgetItemIds): array;
}
