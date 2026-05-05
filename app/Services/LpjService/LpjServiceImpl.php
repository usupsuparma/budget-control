<?php

namespace App\Services\LpjService;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionLpjSubmission;
use App\Models\LpjApprovalMaster;
use App\Models\LpjApprovalDetail;
use App\Services\BudgetLedgerService\BudgetLedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LpjServiceImpl implements LpjService
{
    protected BudgetLedgerService $budgetLedgerService;

    public function __construct(BudgetLedgerService $budgetLedgerService)
    {
        $this->budgetLedgerService = $budgetLedgerService;
    }
    /**
     * Submit LPJ for a transaction.
     */
    public function submitLpj(int $transactionId, array $data): array
    {
        try {
            DB::beginTransaction();

            $transaction = Transaction::with('details')->find($transactionId);

            if (!$transaction) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Transaction not found'];
            }

            // Validate transaction status. LPJ can be submitted after transaction approval is completed.
            if (! in_array($transaction->status, [Transaction::STATUS_APPROVED, Transaction::STATUS_PAID], true)) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Transaction must be APPROVED or PAID to submit LPJ'];
            }

            // Check if LPJ already exists
            if ($transaction->lpjSubmission) {
                DB::rollBack();
                return ['success' => false, 'message' => 'LPJ already submitted for this transaction'];
            }

            // Get active approvers
            $approvers = LpjApprovalMaster::getActiveApprovers();

            if ($approvers->isEmpty()) {
                DB::rollBack();
                return ['success' => false, 'message' => 'No LPJ approvers configured. Please contact administrator.'];
            }

            // Handle file upload if provided
            $proofPath = null;
            if (isset($data['proof_of_payment']) && $data['proof_of_payment']) {
                $file = $data['proof_of_payment'];
                $proofPath = $file->store('lpj_proofs', 'public');
            }

            // Create LPJ submission
            $lpjSubmission = TransactionLpjSubmission::create([
                'transaction_id' => $transactionId,
                'submission_date' => $data['submission_date'],
                'realization_date' => $data['realization_date'],
                'proof_of_payment' => $proofPath,
                'status_approval' => TransactionLpjSubmission::STATUS_PENDING,
                'current_approval_level' => 0,
                'total_approval_levels' => $approvers->count(),
            ]);

            // Create approval chain snapshot
            foreach ($approvers as $approver) {
                LpjApprovalDetail::create([
                    'lpj_submission_id' => $lpjSubmission->id,
                    'employment_id' => $approver->employment_id,
                    'level_sequence' => $approver->approval_sequence,
                    'status' => LpjApprovalDetail::STATUS_PENDING,
                ]);
            }

            // Update transaction details with realization values
            if (isset($data['items']) && is_array($data['items'])) {
                $totalActual = 0;
                foreach ($data['items'] as $item) {
                    $detail = TransactionDetail::find($item['detail_id']);
                    if ($detail && $detail->transaction_id == $transactionId) {
                        $fixTotal = $item['fix_quantity'] * $item['fix_price'];
                        $detail->update([
                            'fix_quantity' => $item['fix_quantity'],
                            'fix_price' => $item['fix_price'],
                            'fix_total' => $fixTotal,
                        ]);
                        $totalActual += $fixTotal;
                    }
                }

                // Update transaction actual_amount
                $transaction->update(['actual_amount' => $totalActual]);
            }

            // Update LPJ status to in_progress (first approver can now act)
            $lpjSubmission->update(['status_approval' => TransactionLpjSubmission::STATUS_IN_PROGRESS]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'LPJ submitted successfully',
                'data' => $lpjSubmission->load('approvalDetails.employment.employee')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('LPJ submission error: ' . $e->getMessage(), [
                'transaction_id' => $transactionId,
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => 'Failed to submit LPJ: ' . $e->getMessage()];
        }
    }

    /**
     * Get LPJ form data for a transaction.
     */
    public function getLpjFormData(int $transactionId): array
    {
        try {
            $transaction = Transaction::with([
                'details',
                'user',
                'unit',
                'jobLevel',
                'jobPosition',
                'lpjSubmission.approvalDetails.employment.employee'
            ])->find($transactionId);

            if (!$transaction) {
                return ['success' => false, 'message' => 'Transaction not found'];
            }

            // Check transaction status
            if ($transaction->status !== Transaction::STATUS_PAID && $transaction->status !== Transaction::STATUS_APPROVED) {
                return ['success' => false, 'message' => 'Transaction must be APPROVED or PAID to access LPJ form'];
            }

            return [
                'success' => true,
                'data' => [
                    'transaction' => $transaction,
                    'details' => $transaction->details,
                    'lpj_submission' => $transaction->lpjSubmission,
                    'can_submit' => $transaction->canSubmitLpj(),
                    'has_lpj' => $transaction->lpjSubmission !== null,
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Get LPJ form data error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to get LPJ form data'];
        }
    }

    /**
     * Process an LPJ approval action.
     */
    /**
     * {@inheritdoc}
     */
    public function willBeFullyApprovedAfter(int $lpjSubmissionId, int $employmentId): bool
    {
        $lpj = TransactionLpjSubmission::with('approvalDetails')->find($lpjSubmissionId);

        if (! $lpj || $lpj->isApproved() || $lpj->isRejected()) {
            return false;
        }

        $currentPending = $lpj->getCurrentPendingApproval();

        if (! $currentPending || (int) $currentPending->employment_id !== $employmentId) {
            return false;
        }

        return ($lpj->current_approval_level + 1) >= $lpj->total_approval_levels;
    }

    public function processApproval(int $lpjSubmissionId, string $action, int $employmentId, ?string $notes = null): array
    {
        try {
            DB::beginTransaction();

            $lpjSubmission = TransactionLpjSubmission::with(['approvalDetails', 'transaction'])
                ->find($lpjSubmissionId);

            if (!$lpjSubmission) {
                DB::rollBack();
                return ['success' => false, 'message' => 'LPJ submission not found'];
            }

            if ($lpjSubmission->isApproved() || $lpjSubmission->isRejected()) {
                DB::rollBack();
                return ['success' => false, 'message' => 'LPJ has already been ' . $lpjSubmission->status_approval];
            }

            // Find the current pending approval detail for this user
            $approvalDetail = $lpjSubmission->approvalDetails
                ->where('employment_id', $employmentId)
                ->where('status', LpjApprovalDetail::STATUS_PENDING)
                ->first();

            if (!$approvalDetail) {
                DB::rollBack();
                return ['success' => false, 'message' => 'You are not authorized to approve this LPJ'];
            }

            // Check if it's this user's turn (sequence check)
            $currentPending = $lpjSubmission->getCurrentPendingApproval();
            if (!$currentPending || $currentPending->id !== $approvalDetail->id) {
                DB::rollBack();
                return ['success' => false, 'message' => 'It is not your turn to approve. Please wait for previous approvers.'];
            }

            if ($action === 'approve') {
                // Approve this level
                $approvalDetail->update([
                    'status' => LpjApprovalDetail::STATUS_APPROVED,
                    'notes' => $notes,
                    'actioned_at' => now(),
                ]);

                // Increment approval level
                $lpjSubmission->increment('current_approval_level');

                // Check if all levels approved
                if ($lpjSubmission->current_approval_level >= $lpjSubmission->total_approval_levels) {
                    // Fully approved
                    $lpjSubmission->update([
                        'status_approval' => TransactionLpjSubmission::STATUS_APPROVED,
                        'approved_at' => now(),
                        'approved_by' => $employmentId,
                    ]);

                    // Update transaction status to PAID
                    $lpjSubmission->transaction->update([
                        'status' => Transaction::STATUS_PAID,
                    ]);

                    // === BUDGET LEDGER: Phase 3 - Record LPJ Settlement mutations ===
                    $mutationResult = $this->budgetLedgerService->recordLpjSettlementMutations(
                        $lpjSubmission->transaction_id,
                        $lpjSubmission->id
                    );
                    if (!$mutationResult['success']) {
                        Log::warning('Failed to record LPJ settlement mutations (non-blocking)', [
                            'transaction_id' => $lpjSubmission->transaction_id,
                            'lpj_submission_id' => $lpjSubmission->id,
                            'error' => $mutationResult['message'],
                        ]);
                    }

                    DB::commit();
                    return [
                        'success' => true,
                        'message' => 'LPJ fully approved. Transaction paid.',
                        'data' => $lpjSubmission->fresh(['approvalDetails.employment.employee', 'transaction'])
                    ];
                }

                DB::commit();
                return [
                    'success' => true,
                    'message' => 'LPJ approved at level ' . $lpjSubmission->current_approval_level,
                    'data' => $lpjSubmission->fresh(['approvalDetails.employment.employee'])
                ];

            } elseif ($action === 'reject') {
                if (empty($notes)) {
                    DB::rollBack();
                    return ['success' => false, 'message' => 'Rejection reason is required'];
                }

                // Reject this level
                $approvalDetail->update([
                    'status' => LpjApprovalDetail::STATUS_REJECTED,
                    'notes' => $notes,
                    'actioned_at' => now(),
                ]);

                // Update LPJ submission status
                $lpjSubmission->update([
                    'status_approval' => TransactionLpjSubmission::STATUS_REJECTED,
                    'rejection_reason' => $notes,
                ]);

                DB::commit();
                return [
                    'success' => true,
                    'message' => 'LPJ rejected',
                    'data' => $lpjSubmission->fresh(['approvalDetails.employment.employee'])
                ];
            }

            DB::rollBack();
            return ['success' => false, 'message' => 'Invalid action'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('LPJ approval error: ' . $e->getMessage(), [
                'lpj_submission_id' => $lpjSubmissionId,
                'action' => $action,
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => 'Failed to process approval: ' . $e->getMessage()];
        }
    }

    /**
     * Get LPJ details including approval status.
     */
    public function getLpjDetails(int $lpjSubmissionId): array
    {
        try {
            $lpjSubmission = TransactionLpjSubmission::with([
                'transaction.details',
                'transaction.user',
                'approvalDetails.employment.employee',
                'finalApprover.employee'
            ])->find($lpjSubmissionId);

            if (!$lpjSubmission) {
                return ['success' => false, 'message' => 'LPJ submission not found'];
            }

            return [
                'success' => true,
                'data' => $lpjSubmission
            ];
        } catch (\Exception $e) {
            Log::error('Get LPJ details error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to get LPJ details'];
        }
    }

    /**
     * Get pending LPJ approvals for a user.
     */
    public function getPendingLpjApprovalsForUser(int $employmentId, array $filters = []): array
    {
        try {
            $status = $filters['status'] ?? 'pending';

            $query = LpjApprovalDetail::with([
                'lpjSubmission.transaction.details',
                'lpjSubmission.transaction.user',
                'lpjSubmission.approvalDetails.employment.employee'
            ])->where('employment_id', $employmentId);

            if ($status === 'approved') {
                $approvalDetails = $query
                    ->where('status', LpjApprovalDetail::STATUS_APPROVED)
                    ->latest('actioned_at')
                    ->get();

                return [
                    'success' => true,
                    'data' => $approvalDetails->values(),
                    'count' => $approvalDetails->count()
                ];
            }

            if ($status === 'rejected') {
                $approvalDetails = $query
                    ->where('status', LpjApprovalDetail::STATUS_REJECTED)
                    ->latest('actioned_at')
                    ->get();

                return [
                    'success' => true,
                    'data' => $approvalDetails->values(),
                    'count' => $approvalDetails->count()
                ];
            }

            $query
                ->where('status', LpjApprovalDetail::STATUS_PENDING)
                ->whereHas('lpjSubmission', function ($q) {
                    $q->where('status_approval', TransactionLpjSubmission::STATUS_IN_PROGRESS);
                });

            // Filter pending approvals to only show when it is this user's turn.
            $approvalDetails = $query->get()->filter(function ($detail) {
                $currentPending = $detail->lpjSubmission->getCurrentPendingApproval();
                return $currentPending && $currentPending->id === $detail->id;
            });

            return [
                'success' => true,
                'data' => $approvalDetails->values(),
                'count' => $approvalDetails->count()
            ];
        } catch (\Exception $e) {
            Log::error('Get pending LPJ approvals error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to get pending approvals', 'data' => [], 'count' => 0];
        }
    }

    /**
     * Get LPJ approval counts for a user.
     */
    public function getLpjApprovalCounts(int $employmentId, array $filters = []): array
    {
        try {
            // Pending count (where it's user's turn)
            $pendingResult = $this->getPendingLpjApprovalsForUser($employmentId, $filters);
            $pendingCount = $pendingResult['count'] ?? 0;

            // Approved count
            $approvedCount = LpjApprovalDetail::where('employment_id', $employmentId)
                ->where('status', LpjApprovalDetail::STATUS_APPROVED)
                ->count();

            // Rejected count
            $rejectedCount = LpjApprovalDetail::where('employment_id', $employmentId)
                ->where('status', LpjApprovalDetail::STATUS_REJECTED)
                ->count();

            return [
                'success' => true,
                'data' => [
                    'pending' => $pendingCount,
                    'approved' => $approvedCount,
                    'rejected' => $rejectedCount,
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Get LPJ approval counts error: ' . $e->getMessage());
            return ['success' => false, 'data' => ['pending' => 0, 'approved' => 0, 'rejected' => 0]];
        }
    }

    /**
     * Update LPJ realization items.
     */
    public function updateLpjItems(int $lpjSubmissionId, array $items): array
    {
        try {
            DB::beginTransaction();

            $lpjSubmission = TransactionLpjSubmission::with('transaction.details')->find($lpjSubmissionId);

            if (!$lpjSubmission) {
                return ['success' => false, 'message' => 'LPJ submission not found'];
            }

            // Only allow update if status is pending or in_progress
            if (!$lpjSubmission->isPending()) {
                return ['success' => false, 'message' => 'Cannot update LPJ that is already ' . $lpjSubmission->status_approval];
            }

            $totalActual = 0;
            foreach ($items as $item) {
                $detail = TransactionDetail::find($item['detail_id']);
                if ($detail && $detail->transaction_id == $lpjSubmission->transaction_id) {
                    $fixTotal = $item['fix_quantity'] * $item['fix_price'];
                    $detail->update([
                        'fix_quantity' => $item['fix_quantity'],
                        'fix_price' => $item['fix_price'],
                        'fix_total' => $fixTotal,
                    ]);
                    $totalActual += $fixTotal;
                }
            }

            // Update transaction actual_amount
            $lpjSubmission->transaction->update(['actual_amount' => $totalActual]);

            DB::commit();

            return ['success' => true, 'message' => 'LPJ items updated successfully'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update LPJ items error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update LPJ items'];
        }
    }

    /**
     * Get LPJ by transaction ID.
     */
    public function getLpjByTransactionId(int $transactionId): array
    {
        try {
            $lpjSubmission = TransactionLpjSubmission::with([
                'transaction.details',
                'transaction.user',
                'approvalDetails.employment.employee',
                'finalApprover.employee'
            ])
                ->where('transaction_id', $transactionId)
                ->first();

            if (!$lpjSubmission) {
                return ['success' => false, 'message' => 'LPJ not found for this transaction', 'data' => null];
            }

            return [
                'success' => true,
                'data' => $lpjSubmission
            ];
        } catch (\Exception $e) {
            Log::error('Get LPJ by transaction ID error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to get LPJ', 'data' => null];
        }
    }
}
