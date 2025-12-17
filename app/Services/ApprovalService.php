<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionApproval;
use App\Models\TransactionApprovalLog;
use App\Models\TransactionApprovalThreshold;
use App\Models\TransactionAuthorizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ApprovalService
{
    /**
     * Determine approval flow based on amount
     * 
     * @param float $amount
     * @return TransactionApprovalThreshold|null
     */
    public function determineApprovalFlow($amount)
    {
        return TransactionApprovalThreshold::forAmount($amount)->first();
    }

    /**
     * Create approval chain for a transaction
     * 
     * @param int $transactionId
     * @return array
     */
    public function createApprovalChain($transactionId)
    {
        try {
            $transaction = Transaction::findOrFail($transactionId);
            
            // Determine threshold based on estimated_amount
            $threshold = $this->determineApprovalFlow($transaction->estimated_amount);
            
            if (!$threshold) {
                throw new Exception("No threshold found for amount: {$transaction->estimated_amount}");
            }

            DB::beginTransaction();

            // Update transaction with threshold info
            $transaction->update([
                'threshold_id' => $threshold->id,
                'current_approval_level' => 0,
                'required_approval_levels' => $threshold->approval_sequence,
                'status' => Transaction::STATUS_IN_PROGRESS,
            ]);

            // Get required levels from threshold
            $requiredLevels = $threshold->required_levels ?? [];

            // Create approval records for each required level
            $approvals = [];
            $sequenceOrder = 1;

            foreach ($requiredLevels as $level) {
                // Get authorizer for this level
                $authorizer = TransactionAuthorizer::active()
                    ->byLevel($level)
                    ->ordered()
                    ->first();

                if (!$authorizer) {
                    Log::warning("No authorizer found for level {$level}");
                    continue;
                }

                $approval = TransactionApproval::create([
                    'transaction_id' => $transaction->id,
                    'approver_id' => $authorizer->employee_id,
                    'approver_name' => $authorizer->authorizer_name,
                    'approval_level' => $level,
                    'threshold_id' => $threshold->id,
                    'is_required' => true,
                    'sequence_order' => $sequenceOrder,
                    'status' => TransactionApproval::STATUS_PENDING,
                    'notified_at' => now(),
                    'reminder_count' => 0,
                ]);

                $approvals[] = $approval;
                $sequenceOrder++;
            }

            // Create log entry
            $this->createLog($transaction->id, null, 'create', $transaction->user_id, $transaction->user_name, [
                'threshold_id' => $threshold->id,
                'required_levels' => $requiredLevels,
                'approval_count' => count($approvals),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Approval chain created successfully',
                'transaction' => $transaction->fresh(),
                'approvals' => $approvals,
                'threshold' => $threshold,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Create approval chain failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process approval action (approve/reject)
     * 
     * @param int $approvalId
     * @param int $status
     * @param int $approverId
     * @param string $approverName
     * @param string|null $comments
     * @param string|null $ipAddress
     * @return array
     */
    public function processApproval($approvalId, $status, $approverId, $approverName, $comments = null, $ipAddress = null)
    {
        try {
            $approval = TransactionApproval::with('transaction')->findOrFail($approvalId);
            $transaction = $approval->transaction;

            // Validate approval can be processed
            $validation = $this->validateApprovalAction($approval, $approverId);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message'],
                ];
            }

            DB::beginTransaction();

            $fromStatus = $approval->status;

            // Update approval record
            $approval->update([
                'status' => $status,
                'approved_at' => now(),
                'comments' => $comments,
                'approval_method' => 'web',
                'ip_address' => $ipAddress,
            ]);

            // Handle based on action
            if ($status === TransactionApproval::STATUS_APPROVED) {
                $result = $this->handleApprove($transaction, $approval);
            } elseif ($status === TransactionApproval::STATUS_REJECTED) {
                $result = $this->handleReject($transaction, $approval, $comments);
            } else {
                throw new Exception("Invalid approval status: {$status}");
            }

            // Create log entry
            $action = $status === TransactionApproval::STATUS_APPROVED ? 'approve' : 'reject';
            $this->createLog(
                $transaction->id,
                $approval->id,
                $action,
                $approverId,
                $approverName,
                [
                    'approval_level' => $approval->approval_level,
                    'comments' => $comments,
                ],
                $fromStatus,
                $status,
                $comments,
                $ipAddress
            );

            DB::commit();

            return array_merge(['success' => true], $result);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Process approval failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle approval action
     * 
     * @param Transaction $transaction
     * @param TransactionApproval $approval
     * @return array
     */
    protected function handleApprove(Transaction $transaction, TransactionApproval $approval)
    {
        // Increment current approval level
        $newLevel = $transaction->current_approval_level + 1;
        
        $updateData = [
            'current_approval_level' => $newLevel,
        ];

        // Check if all approvals are complete
        if ($newLevel >= $transaction->required_approval_levels) {
            $updateData['status'] = Transaction::STATUS_APPROVED;
            $updateData['approval_completed_at'] = now();
            $message = 'Transaction fully approved';
        } else {
            $message = "Approval level {$approval->approval_level} completed. Waiting for next approver.";
            
            // Notify next approver
            $this->notifyNextApprover($transaction->id);
        }

        $transaction->update($updateData);

        return [
            'message' => $message,
            'transaction' => $transaction->fresh(),
            'is_fully_approved' => $newLevel >= $transaction->required_approval_levels,
        ];
    }

    /**
     * Handle rejection action
     * 
     * @param Transaction $transaction
     * @param TransactionApproval $approval
     * @param string|null $reason
     * @return array
     */
    protected function handleReject(Transaction $transaction, TransactionApproval $approval, $reason = null)
    {
        // Update transaction status to rejected
        $transaction->update([
            'status' => Transaction::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);

        // Mark all pending approvals as skipped
        TransactionApproval::where('transaction_id', $transaction->id)
            ->where('status', TransactionApproval::STATUS_PENDING)
            ->update(['status' => TransactionApproval::STATUS_SKIPPED]);

        return [
            'message' => 'Transaction rejected',
            'transaction' => $transaction->fresh(),
            'rejected_by_level' => $approval->approval_level,
        ];
    }

    /**
     * Validate if approval action can be performed
     * 
     * @param TransactionApproval $approval
     * @param int $approverId
     * @return array
     */
    protected function validateApprovalAction(TransactionApproval $approval, $approverId)
    {
        // Check if approval is still pending
        if ($approval->status !== TransactionApproval::STATUS_PENDING) {
            return [
                'valid' => false,
                'message' => 'This approval has already been processed',
            ];
        }

        // Check if transaction is still in progress
        if ($approval->transaction->status !== Transaction::STATUS_IN_PROGRESS) {
            return [
                'valid' => false,
                'message' => 'Transaction is not in approval process',
            ];
        }

        // Check if this is the next approval in sequence
        $nextApproval = TransactionApproval::where('transaction_id', $approval->transaction_id)
            ->where('status', TransactionApproval::STATUS_PENDING)
            ->orderBy('sequence_order')
            ->first();

        if ($nextApproval && $nextApproval->id !== $approval->id) {
            return [
                'valid' => false,
                'message' => 'Previous approval levels must be completed first',
            ];
        }

        // Check if approver is authorized
        if ($approval->approver_id !== $approverId) {
            // Check if the user has override permission
            $authorizer = TransactionAuthorizer::where('employee_id', $approverId)
                ->where('can_override', true)
                ->first();
            
            if (!$authorizer) {
                return [
                    'valid' => false,
                    'message' => 'You are not authorized to approve this transaction',
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Notify next approver
     * 
     * @param int $transactionId
     * @return void
     */
    protected function notifyNextApprover($transactionId)
    {
        $nextApproval = TransactionApproval::where('transaction_id', $transactionId)
            ->where('status', TransactionApproval::STATUS_PENDING)
            ->orderBy('sequence_order')
            ->first();

        if ($nextApproval) {
            $nextApproval->update([
                'notified_at' => now(),
            ]);

            // TODO: Send email/push notification to next approver
            Log::info("Notification sent to approver: {$nextApproval->approver_name} for transaction: {$transactionId}");
        }
    }

    /**
     * Get pending approvals for a user
     * 
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingApprovalsForUser($userId)
    {
        return TransactionApproval::with(['transaction', 'threshold'])
            ->where('approver_id', $userId)
            ->where('status', TransactionApproval::STATUS_PENDING)
            ->whereHas('transaction', function ($query) {
                $query->where('status', Transaction::STATUS_IN_PROGRESS);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->filter(function ($approval) {
                // Only return if this is the next in sequence
                $nextApproval = TransactionApproval::where('transaction_id', $approval->transaction_id)
                    ->where('status', TransactionApproval::STATUS_PENDING)
                    ->orderBy('sequence_order')
                    ->first();
                
                return $nextApproval && $nextApproval->id === $approval->id;
            });
    }

    /**
     * Get approval history for a transaction
     * 
     * @param int $transactionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getApprovalHistory($transactionId)
    {
        return TransactionApproval::with('logs')
            ->where('transaction_id', $transactionId)
            ->orderBy('sequence_order')
            ->get();
    }

    /**
     * Get approval logs for a transaction
     * 
     * @param int $transactionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getApprovalLogs($transactionId)
    {
        return TransactionApprovalLog::where('transaction_id', $transactionId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create approval log entry
     * 
     * @param int $transactionId
     * @param int|null $approvalId
     * @param string $action
     * @param int $actorId
     * @param string $actorName
     * @param array|null $metadata
     * @param int|null $fromStatus
     * @param int|null $toStatus
     * @param string|null $notes
     * @param string|null $ipAddress
     * @return TransactionApprovalLog
     */
    protected function createLog(
        $transactionId,
        $approvalId,
        $action,
        $actorId,
        $actorName,
        $metadata = null,
        $fromStatus = null,
        $toStatus = null,
        $notes = null,
        $ipAddress = null
    ) {
        return TransactionApprovalLog::create([
            'transaction_id' => $transactionId,
            'approval_id' => $approvalId,
            'action' => $action,
            'actor_id' => $actorId,
            'actor_name' => $actorName,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'notes' => $notes,
            'metadata' => $metadata,
            'ip_address' => $ipAddress,
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Cancel a transaction
     * 
     * @param int $transactionId
     * @param int $userId
     * @param string $userName
     * @param string|null $reason
     * @return array
     */
    public function cancelTransaction($transactionId, $userId, $userName, $reason = null)
    {
        try {
            $transaction = Transaction::findOrFail($transactionId);

            // Only pending or in-progress transactions can be cancelled
            if (!in_array($transaction->status, [Transaction::STATUS_PENDING, Transaction::STATUS_IN_PROGRESS])) {
                return [
                    'success' => false,
                    'message' => 'Only pending or in-progress transactions can be cancelled',
                ];
            }

            DB::beginTransaction();

            $fromStatus = $transaction->status;

            $transaction->update([
                'status' => Transaction::STATUS_CANCELLED,
                'rejection_reason' => $reason,
            ]);

            // Mark all pending approvals as skipped
            TransactionApproval::where('transaction_id', $transactionId)
                ->where('status', TransactionApproval::STATUS_PENDING)
                ->update(['status' => TransactionApproval::STATUS_SKIPPED]);

            // Create log entry
            $this->createLog(
                $transactionId,
                null,
                'cancel',
                $userId,
                $userName,
                ['reason' => $reason],
                $fromStatus,
                Transaction::STATUS_CANCELLED,
                $reason
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Transaction cancelled successfully',
                'transaction' => $transaction->fresh(),
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Cancel transaction failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Recalculate approval chain when amount changes
     * 
     * @param int $transactionId
     * @param float $newAmount
     * @return array
     */
    public function recalculateApprovalChain($transactionId, $newAmount)
    {
        try {
            $transaction = Transaction::findOrFail($transactionId);

            // Only pending transactions can have their approval chain recalculated
            if ($transaction->status !== Transaction::STATUS_PENDING) {
                return [
                    'success' => false,
                    'message' => 'Only pending transactions can have their approval chain recalculated',
                ];
            }

            // Delete existing approvals
            TransactionApproval::where('transaction_id', $transactionId)->delete();

            // Update amount
            $transaction->update(['estimated_amount' => $newAmount]);

            // Recreate approval chain
            return $this->createApprovalChain($transactionId);

        } catch (Exception $e) {
            Log::error('Recalculate approval chain failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get approval statistics
     * 
     * @param int|null $userId
     * @return array
     */
    public function getApprovalStatistics($userId = null)
    {
        $query = Transaction::query();

        $stats = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->pending()->count(),
            'in_progress' => (clone $query)->inProgress()->count(),
            'approved' => (clone $query)->approved()->count(),
            'rejected' => (clone $query)->rejected()->count(),
        ];

        if ($userId) {
            $pendingForUser = $this->getPendingApprovalsForUser($userId)->count();
            $stats['pending_for_user'] = $pendingForUser;
        }

        return $stats;
    }
}
