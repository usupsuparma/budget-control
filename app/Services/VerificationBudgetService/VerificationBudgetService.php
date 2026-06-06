<?php

namespace App\Services\VerificationBudgetService;

interface VerificationBudgetService
{
    /**
     * Submit budget item for verification (Phase 1)
     * Creates snapshot of eligible verifiers based on cost_center mapping
     * 
     * @param int $itemId WorkplanBudgetItem ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function submitForVerification(int $itemId): array;

    /**
     * Cancel pending budget item verification before any verifier processes it.
     *
     * @param int $itemId WorkplanBudgetItem ID
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function cancelVerification(int $itemId): array;

    /**
     * Verify budget item (set fix price and auto-submit for approval)
     * 
     * @param int $itemId WorkplanBudgetItem ID
     * @param float $fixPrice Verified total price
     * @param string|null $notes Verification notes
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function verifyBudget(int $itemId, float $fixPrice, ?string $notes = null): array;

    /**
     * Reject verification
     * 
     * @param int $itemId WorkplanBudgetItem ID
     * @param string $notes Rejection reason
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function rejectVerification(int $itemId, string $notes): array;

    /**
     * Get items pending verification for current user
     * 
     * @return array
     */
    public function getMyPendingVerifications(): array;

    /**
     * Get verification history for items verified by current user
     * 
     * @return array
     */
    public function getMyVerifiedVerifications(): array;

    /**
     * Get verification status for an item
     * 
     * @param int $itemId WorkplanBudgetItem ID
     * @return array
     */
    public function getVerificationStatus(int $itemId): array;

    /**
     * Get eligible verifiers by cost_center code
     * 
     * @param string $costCenter
     * @return array List of employee_ids
     */
    public function getVerifiersByCostCenter(string $costCenter): array;

    /**
     * Bulk verify budget items
     * 
     * @param array $itemIds List of WorkplanBudgetItem IDs
     * @param array $fixPrices Map of [item_id => fix_price]
     * @param string|null $notes General verification notes
     * @return array
     */
    public function bulkVerify(array $itemIds, array $fixPrices = [], ?string $notes = null): array;

    /**
     * Bulk reject verification
     * 
     * @param array $itemIds List of WorkplanBudgetItem IDs
     * @param string $notes Rejection reason
     * @return array
     */
    public function bulkReject(array $itemIds, string $notes): array;

    /**
     * Process CSV import for bulk verification
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @return array
     */
    public function processCsvImport($file): array;

    /**
     * Check if current user can verify an item
     * 
     * @param int $itemId WorkplanBudgetItem ID
     * @return bool
     */
    public function canVerify(int $itemId): bool;
}
