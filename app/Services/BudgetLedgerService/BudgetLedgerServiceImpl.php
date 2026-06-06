<?php

namespace App\Services\BudgetLedgerService;

use App\Models\BudgetMutation;
use App\Models\BudgetSubmission;
use App\Models\Transaction;
use App\Models\WorkplanBudgetItem;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetLedgerServiceImpl implements BudgetLedgerService
{
    /**
     * Phase 0: Record Initial Budget (Saldo Awal) when WorkplanBudgetItem is fully approved.
     *
     * Trigger: WorkplanBudgetItem fully approved (all approval levels done).
     * Action: Insert CREDIT mutation with amount = fix_price_total (verified price).
     */
    public function recordInitialBudgetMutation(int $budgetItemId): array
    {
        try {
            $budgetItem = WorkplanBudgetItem::find($budgetItemId);

            if (! $budgetItem) {
                return ['success' => false, 'message' => 'Budget item tidak ditemukan.'];
            }

            // Check if already recorded
            $existingMutation = BudgetMutation::where('workplan_budget_item_id', $budgetItemId)
                ->where('category', BudgetMutation::CATEGORY_INITIAL_BUDGET)
                ->exists();

            if ($existingMutation) {
                return [
                    'success' => false,
                    'message' => 'Saldo awal untuk budget item ini sudah pernah dicatat.',
                ];
            }

            // Use fix_price_total as the initial budget amount
            $amount = $budgetItem->total ?? 0;

            if ($amount <= 0) {
                return [
                    'success' => false,
                    'message' => 'Fix price total harus lebih dari 0 untuk mencatat saldo awal.',
                ];
            }

            $mutation = BudgetMutation::create([
                'workplan_budget_item_id' => $budgetItemId,
                'transaction_id' => null, // No transaction for initial budget
                'transaction_detail_id' => null,
                'transaction_lpj_submission_id' => null,
                'mutation_type' => BudgetMutation::TYPE_CREDIT, // Initial budget is CREDIT (incoming)
                'amount' => $amount,
                'category' => BudgetMutation::CATEGORY_INITIAL_BUDGET,
                'description' => "Saldo Awal: {$amount} untuk Budget Item ID {$budgetItemId}",
                'created_at' => now(),
            ]);

            Log::info('Initial budget mutation recorded', [
                'budget_item_id' => $budgetItemId,
                'amount' => $amount,
            ]);

            return [
                'success' => true,
                'message' => 'Saldo awal berhasil dicatat.',
                'data' => $mutation,
            ];
        } catch (Exception $e) {
            Log::error('Failed to record initial budget mutation: '.$e->getMessage(), [
                'BudgetLedgerServiceImpl.recordInitialBudgetMutation',
                'budget_item_id' => $budgetItemId,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mencatat saldo awal: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Phase 1: Record Cash Advance debit mutations when transaction is fully approved.
     *
     * Trigger: Transaction fully approved (all approval levels done).
     * Action: Loop all transaction_details, insert DEBIT mutation per detail.
     */
    public function recordCashAdvanceMutations(int $transactionId): array
    {
        try {
            $transaction = Transaction::with('details')->find($transactionId);

            if (! $transaction) {
                return ['success' => false, 'message' => 'Transaksi tidak ditemukan.'];
            }

            // Prevent duplicate mutations for same transaction + category
            $existingMutations = BudgetMutation::where('transaction_id', $transactionId)
                ->where('category', BudgetMutation::CATEGORY_CASH_ADVANCE)
                ->exists();

            if ($existingMutations) {
                return [
                    'success' => false,
                    'message' => 'Mutasi Cash Advance untuk transaksi ini sudah pernah dicatat.',
                ];
            }

            $mutations = [];

            foreach ($transaction->details as $detail) {
                // Skip details without budget_id (cannot link to workplan_budget_item)
                if (empty($detail->budget_id)) {
                    Log::warning('Transaction detail has no budget_id, skipping mutation', [
                        'transaction_detail_id' => $detail->id,
                        'transaction_id' => $transactionId,
                    ]);

                    continue;
                }

                // Skip details with zero or null estimated_total
                if (empty($detail->estimated_total) || $detail->estimated_total <= 0) {
                    continue;
                }

                $mutation = BudgetMutation::create([
                    'workplan_budget_item_id' => $detail->budget_id,
                    'transaction_id' => $transactionId,
                    'transaction_detail_id' => $detail->id,
                    'transaction_lpj_submission_id' => null,
                    'mutation_type' => BudgetMutation::TYPE_DEBIT,
                    'amount' => $detail->estimated_total,
                    'category' => BudgetMutation::CATEGORY_CASH_ADVANCE,
                    'description' => "Cash Advance: {$transaction->transaction_number} - {$detail->goods_service_name}",
                    'created_at' => now(),
                ]);

                $mutations[] = $mutation;
            }

            Log::info('Cash Advance mutations recorded', [
                'transaction_id' => $transactionId,
                'mutation_count' => count($mutations),
            ]);

            return [
                'success' => true,
                'message' => 'Mutasi Cash Advance berhasil dicatat ('.count($mutations).' item).',
                'data' => $mutations,
            ];
        } catch (Exception $e) {
            Log::error('Failed to record Cash Advance mutations: '.$e->getMessage(), [
                'transaction_id' => $transactionId,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mencatat mutasi Cash Advance: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Phase 3: Record LPJ Settlement mutations when LPJ is fully approved.
     *
     * Trigger: LPJ fully approved by all approvers.
     * Action: Calculate selisih per detail, create CREDIT (refund) or DEBIT (reimburse).
     */
    public function recordLpjSettlementMutations(int $transactionId, int $lpjSubmissionId): array
    {
        try {
            $transaction = Transaction::with('details')->find($transactionId);

            if (! $transaction) {
                return ['success' => false, 'message' => 'Transaksi tidak ditemukan.'];
            }

            // Prevent duplicate LPJ settlement mutations
            $existingSettlement = BudgetMutation::where('transaction_id', $transactionId)
                ->where('transaction_lpj_submission_id', $lpjSubmissionId)
                ->whereIn('category', [
                    BudgetMutation::CATEGORY_LPJ_REFUND,
                    BudgetMutation::CATEGORY_LPJ_REIMBURSE,
                ])
                ->exists();

            if ($existingSettlement) {
                return [
                    'success' => false,
                    'message' => 'Mutasi settlement LPJ untuk transaksi ini sudah pernah dicatat.',
                ];
            }

            $mutations = [];

            foreach ($transaction->details as $detail) {
                // Skip details without budget_id
                if (empty($detail->budget_id)) {
                    continue;
                }

                $estimatedTotal = (float) $detail->estimated_total;
                $fixTotal = (float) $detail->fix_total;

                // Skip if fix_total not yet filled
                if ($fixTotal <= 0) {
                    continue;
                }

                $selisih = $estimatedTotal - $fixTotal;

                if ($selisih > 0) {
                    // CASE 1: Uang sisa (hemat) - return to budget
                    $mutation = BudgetMutation::create([
                        'workplan_budget_item_id' => $detail->budget_id,
                        'transaction_id' => $transactionId,
                        'transaction_detail_id' => $detail->id,
                        'transaction_lpj_submission_id' => $lpjSubmissionId,
                        'mutation_type' => BudgetMutation::TYPE_CREDIT,
                        'amount' => abs($selisih),
                        'category' => BudgetMutation::CATEGORY_LPJ_REFUND,
                        'description' => "LPJ Refund: {$transaction->transaction_number} - {$detail->goods_service_name} (hemat ".number_format(abs($selisih), 2).')',
                        'created_at' => now(),
                    ]);
                    $mutations[] = $mutation;

                } elseif ($selisih < 0) {
                    // CASE 2: Uang kurang (overbudget) - additional debit
                    $mutation = BudgetMutation::create([
                        'workplan_budget_item_id' => $detail->budget_id,
                        'transaction_id' => $transactionId,
                        'transaction_detail_id' => $detail->id,
                        'transaction_lpj_submission_id' => $lpjSubmissionId,
                        'mutation_type' => BudgetMutation::TYPE_DEBIT,
                        'amount' => abs($selisih),
                        'category' => BudgetMutation::CATEGORY_LPJ_REIMBURSE,
                        'description' => "LPJ Reimburse: {$transaction->transaction_number} - {$detail->goods_service_name} (kurang ".number_format(abs($selisih), 2).')',
                        'created_at' => now(),
                    ]);
                    $mutations[] = $mutation;
                }
                // If selisih == 0 (pas), no mutation needed
            }

            Log::info('LPJ Settlement mutations recorded', [
                'transaction_id' => $transactionId,
                'lpj_submission_id' => $lpjSubmissionId,
                'mutation_count' => count($mutations),
            ]);

            return [
                'success' => true,
                'message' => 'Mutasi settlement LPJ berhasil dicatat ('.count($mutations).' item).',
                'data' => $mutations,
            ];
        } catch (Exception $e) {
            Log::error('Failed to record LPJ settlement mutations: '.$e->getMessage(), [
                'transaction_id' => $transactionId,
                'lpj_submission_id' => $lpjSubmissionId,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mencatat mutasi settlement LPJ: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Record Add Budget or Relocation Budget mutations from approved budget submission.
     */
    public function recordBudgetSubmissionMovement(int $submissionId): array
    {
        try {
            return DB::transaction(function () use ($submissionId) {
                $submission = BudgetSubmission::lockForUpdate()->find($submissionId);

                if (! $submission) {
                    return ['success' => false, 'message' => 'Budget submission tidak ditemukan.'];
                }

                $existingMutationCount = BudgetMutation::where('budget_submission_id', $submissionId)->count();
                if ($existingMutationCount > 0) {
                    return [
                        'success' => true,
                        'message' => 'Mutasi budget movement untuk submission ini sudah pernah dicatat.',
                        'data' => BudgetMutation::where('budget_submission_id', $submissionId)->get(),
                    ];
                }

                $amount = (float) $submission->estimation_amount;
                if ($amount <= 0) {
                    return ['success' => false, 'message' => 'Nilai budget movement harus lebih dari 0.'];
                }

                $budgetItemIds = collect([
                    $submission->budget_account_id,
                    $submission->source_budget_account_id,
                ])
                    ->filter()
                    ->unique()
                    ->values();

                $budgetItems = WorkplanBudgetItem::whereIn('id', $budgetItemIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $targetBudgetItem = $budgetItems->get($submission->budget_account_id);
                if (! $targetBudgetItem) {
                    return ['success' => false, 'message' => 'Budget item tujuan tidak ditemukan.'];
                }

                $targetValidation = $this->validateSubmissionBudgetItem($targetBudgetItem, $submission->work_plan_id, 'tujuan');
                if (! $targetValidation['success']) {
                    return $targetValidation;
                }

                if ($submission->type === 'add') {
                    $sourceBudgetItem = $budgetItems->get($submission->source_budget_account_id);
                    if (! $sourceBudgetItem) {
                        return ['success' => false, 'message' => 'Budget item sumber wajib dipilih untuk Add Budget.'];
                    }

                    if ($sourceBudgetItem->id === $targetBudgetItem->id) {
                        return ['success' => false, 'message' => 'Budget item sumber dan tujuan Add Budget tidak boleh sama.'];
                    }

                    $sourceValidation = $this->validateSubmissionBudgetItem($sourceBudgetItem, $submission->work_plan_id, 'sumber');
                    if (! $sourceValidation['success']) {
                        return $sourceValidation;
                    }

                    $balanceResult = $this->getBudgetBalance($sourceBudgetItem->id);
                    if (! $balanceResult['success']) {
                        return $balanceResult;
                    }

                    $currentBalance = (float) $balanceResult['data']['current_balance'];
                    if ($amount > $currentBalance) {
                        return [
                            'success' => false,
                            'message' => 'Saldo budget sumber tidak mencukupi. Saldo tersedia Rp '
                                . number_format($currentBalance, 0, ',', '.')
                                . ', nilai Add Budget Rp '
                                . number_format($amount, 0, ',', '.')
                                . '.',
                        ];
                    }

                    $sourceMutation = BudgetMutation::create([
                        'workplan_budget_item_id' => $sourceBudgetItem->id,
                        'transaction_id' => null,
                        'transaction_detail_id' => null,
                        'transaction_lpj_submission_id' => null,
                        'budget_submission_id' => $submission->id,
                        'mutation_type' => BudgetMutation::TYPE_DEBIT,
                        'amount' => $amount,
                        'category' => BudgetMutation::CATEGORY_BUDGET_AMENDMENT,
                        'description' => 'Add Budget Movement #' . $submission->id . ' keluar ke '
                            . BudgetSubmission::formatBudgetItemLabel($targetBudgetItem),
                        'created_at' => now(),
                    ]);

                    $targetMutation = BudgetMutation::create([
                        'workplan_budget_item_id' => $targetBudgetItem->id,
                        'transaction_id' => null,
                        'transaction_detail_id' => null,
                        'transaction_lpj_submission_id' => null,
                        'budget_submission_id' => $submission->id,
                        'mutation_type' => BudgetMutation::TYPE_CREDIT,
                        'amount' => $amount,
                        'category' => BudgetMutation::CATEGORY_BUDGET_AMENDMENT,
                        'description' => 'Add Budget Movement #' . $submission->id . ' masuk dari '
                            . BudgetSubmission::formatBudgetItemLabel($sourceBudgetItem),
                        'created_at' => now(),
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Mutasi add budget berhasil dicatat.',
                        'data' => [$sourceMutation, $targetMutation],
                    ];
                }

                if ($submission->type !== 'relocation') {
                    return ['success' => false, 'message' => 'Tipe budget movement tidak valid.'];
                }

                $sourceBudgetItem = $budgetItems->get($submission->source_budget_account_id);
                if (! $sourceBudgetItem) {
                    return ['success' => false, 'message' => 'Budget item sumber relocation tidak ditemukan.'];
                }

                if ($sourceBudgetItem->id === $targetBudgetItem->id) {
                    return ['success' => false, 'message' => 'Budget item sumber dan tujuan relocation tidak boleh sama.'];
                }

                $sourceValidation = $this->validateSubmissionBudgetItem($sourceBudgetItem, $submission->work_plan_id, 'sumber');
                if (! $sourceValidation['success']) {
                    return $sourceValidation;
                }

                $balanceResult = $this->getBudgetBalance($sourceBudgetItem->id);
                if (! $balanceResult['success']) {
                    return $balanceResult;
                }

                $currentBalance = (float) $balanceResult['data']['current_balance'];
                if ($amount > $currentBalance) {
                    return [
                        'success' => false,
                        'message' => 'Saldo budget sumber tidak mencukupi. Saldo tersedia Rp '
                            . number_format($currentBalance, 0, ',', '.')
                            . ', nilai relocation Rp '
                            . number_format($amount, 0, ',', '.')
                            . '.',
                    ];
                }

                $sourceMutation = BudgetMutation::create([
                    'workplan_budget_item_id' => $sourceBudgetItem->id,
                    'transaction_id' => null,
                    'transaction_detail_id' => null,
                    'transaction_lpj_submission_id' => null,
                    'budget_submission_id' => $submission->id,
                    'mutation_type' => BudgetMutation::TYPE_DEBIT,
                    'amount' => $amount,
                    'category' => BudgetMutation::CATEGORY_BUDGET_RELOCATION_OUT,
                    'description' => 'Relocation Budget Movement #' . $submission->id . ' keluar ke '
                        . BudgetSubmission::formatBudgetItemLabel($targetBudgetItem),
                    'created_at' => now(),
                ]);

                $targetMutation = BudgetMutation::create([
                    'workplan_budget_item_id' => $targetBudgetItem->id,
                    'transaction_id' => null,
                    'transaction_detail_id' => null,
                    'transaction_lpj_submission_id' => null,
                    'budget_submission_id' => $submission->id,
                    'mutation_type' => BudgetMutation::TYPE_CREDIT,
                    'amount' => $amount,
                    'category' => BudgetMutation::CATEGORY_BUDGET_RELOCATION_IN,
                    'description' => 'Relocation Budget Movement #' . $submission->id . ' masuk dari '
                        . BudgetSubmission::formatBudgetItemLabel($sourceBudgetItem),
                    'created_at' => now(),
                ]);

                return [
                    'success' => true,
                    'message' => 'Mutasi relocation budget berhasil dicatat.',
                    'data' => [$sourceMutation, $targetMutation],
                ];
            });
        } catch (Exception $e) {
            Log::error('Failed to record budget submission movement: '.$e->getMessage(), [
                'submission_id' => $submissionId,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mencatat mutasi budget movement: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Validate if budget has sufficient balance for a transaction before approval.
     * Golden Rule #2: Check estimated_total <= current_balance per detail.
     */
    public function validateBudgetSufficiency(int $transactionId): array
    {
        try {
            $transaction = Transaction::with('details')->find($transactionId);

            if (! $transaction) {
                return ['success' => false, 'message' => 'Transaksi tidak ditemukan.'];
            }

            $insufficientItems = [];

            foreach ($transaction->details as $detail) {
                if (empty($detail->budget_id)) {
                    continue;
                }

                $balanceResult = $this->getBudgetBalance($detail->budget_id);

                if (! $balanceResult['success']) {
                    $insufficientItems[] = [
                        'detail_id' => $detail->id,
                        'budget_id' => $detail->budget_id,
                        'budget_name' => $detail->budget_name,
                        'reason' => 'Gagal mengambil data saldo: '.$balanceResult['message'],
                    ];

                    continue;
                }

                $currentBalance = (float) $balanceResult['data']['current_balance'];
                $estimatedTotal = (float) $detail->estimated_total;

                if ($estimatedTotal > $currentBalance) {
                    $insufficientItems[] = [
                        'detail_id' => $detail->id,
                        'budget_id' => $detail->budget_id,
                        'budget_name' => $detail->budget_name,
                        'goods_service_name' => $detail->goods_service_name,
                        'estimated_total' => $estimatedTotal,
                        'current_balance' => $currentBalance,
                        'shortage' => $estimatedTotal - $currentBalance,
                    ];
                }
            }

            if (count($insufficientItems) > 0) {
                return [
                    'success' => false,
                    'message' => 'Saldo anggaran tidak mencukupi untuk '.count($insufficientItems).' item.',
                    'insufficient_items' => $insufficientItems,
                ];
            }

            return [
                'success' => true,
                'message' => 'Saldo anggaran mencukupi untuk semua item.',
                'insufficient_items' => [],
            ];
        } catch (Exception $e) {
            Log::error('Budget sufficiency validation failed: '.$e->getMessage(), [
                'transaction_id' => $transactionId,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memvalidasi saldo anggaran: '.$e->getMessage(),
                'insufficient_items' => [],
            ];
        }
    }

    /**
     * Get current balance for a specific workplan_budget_item.
     * Formula: total_credit - total_debit.
     *
     * INITIAL_BUDGET is already a CREDIT entry in pure ledger mode.
     */
    public function getBudgetBalance(int $budgetItemId): array
    {
        try {
            $budgetItem = WorkplanBudgetItem::find($budgetItemId);

            if (! $budgetItem) {
                return [
                    'success' => false,
                    'message' => 'Budget item tidak ditemukan.',
                ];
            }

            $totalDebit = (float) BudgetMutation::byBudgetItem($budgetItemId)
                ->debit()
                ->sum('amount');
            $totalCredit = (float) BudgetMutation::byBudgetItem($budgetItemId)
                ->credit()
                ->sum('amount');
            $initialBudget = (float) BudgetMutation::byBudgetItem($budgetItemId)
                ->credit()
                ->byCategory(BudgetMutation::CATEGORY_INITIAL_BUDGET)
                ->sum('amount');

            return [
                'success' => true,
                'data' => [
                    'id' => $budgetItem->id,
                    'description' => $budgetItem->description,
                    'initial_budget' => $initialBudget,
                    'planned_budget' => (float) $budgetItem->total,
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'current_balance' => $totalCredit - $totalDebit,
                ],
            ];
        } catch (Exception $e) {
            Log::error('Failed to get budget balance: '.$e->getMessage(), [
                'budget_item_id' => $budgetItemId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengambil saldo anggaran: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get mutation history for a specific budget item.
     */
    public function getMutationHistory(int $budgetItemId, array $filters = []): array
    {
        try {
            $query = BudgetMutation::with(['transaction', 'transactionDetail', 'lpjSubmission'])
                ->where('workplan_budget_item_id', $budgetItemId)
                ->orderBy('created_at', 'desc');

            // Apply filters
            if (! empty($filters['category'])) {
                $query->where('category', $filters['category']);
            }

            if (! empty($filters['date_from'])) {
                $query->where('created_at', '>=', $filters['date_from']);
            }

            if (! empty($filters['date_to'])) {
                $query->where('created_at', '<=', $filters['date_to'].' 23:59:59');
            }

            $mutations = $query->get();

            return [
                'success' => true,
                'data' => $mutations,
            ];
        } catch (Exception $e) {
            Log::error('Failed to get mutation history: '.$e->getMessage(), [
                'budget_item_id' => $budgetItemId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengambil riwayat mutasi: '.$e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Get mutation summary for a transaction.
     */
    public function getTransactionMutations(int $transactionId): array
    {
        try {
            $mutations = BudgetMutation::with(['workplanBudgetItem', 'transactionDetail'])
                ->where('transaction_id', $transactionId)
                ->orderBy('created_at', 'asc')
                ->get();

            $summary = [
                'total_debit' => $mutations->where('mutation_type', BudgetMutation::TYPE_DEBIT)->sum('amount'),
                'total_credit' => $mutations->where('mutation_type', BudgetMutation::TYPE_CREDIT)->sum('amount'),
                'net_impact' => 0,
                'mutations' => $mutations,
            ];

            $summary['net_impact'] = $summary['total_debit'] - $summary['total_credit'];

            return [
                'success' => true,
                'data' => $summary,
            ];
        } catch (Exception $e) {
            Log::error('Failed to get transaction mutations: '.$e->getMessage(), [
                'transaction_id' => $transactionId,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengambil data mutasi transaksi: '.$e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Get balance overview for multiple budget items (dashboard use).
     */
    public function getBulkBudgetBalances(array $budgetItemIds): array
    {
        try {
            if (empty($budgetItemIds)) {
                return ['success' => true, 'data' => []];
            }

            $results = DB::table('workplan_budget_items as wbi')
                ->leftJoin('budget_mutations as bm', 'wbi.id', '=', 'bm.workplan_budget_item_id')
                ->whereIn('wbi.id', $budgetItemIds)
                ->whereNull('wbi.deleted_at')
                ->groupBy('wbi.id', 'wbi.description', 'wbi.total')
                ->selectRaw("
                    wbi.id,
                    wbi.description,
                    CAST(wbi.total AS DECIMAL(19,4)) AS planned_budget,
                    COALESCE(SUM(CASE WHEN bm.mutation_type = 'C' AND bm.category = 'INITIAL_BUDGET' THEN bm.amount ELSE 0 END), 0) AS initial_budget,
                    COALESCE(SUM(CASE WHEN bm.mutation_type = 'D' THEN bm.amount ELSE 0 END), 0) AS total_debit,
                    COALESCE(SUM(CASE WHEN bm.mutation_type = 'C' THEN bm.amount ELSE 0 END), 0) AS total_credit,
                    (
                        COALESCE(SUM(CASE WHEN bm.mutation_type = 'C' THEN bm.amount ELSE 0 END), 0)
                        - COALESCE(SUM(CASE WHEN bm.mutation_type = 'D' THEN bm.amount ELSE 0 END), 0)
                    ) AS current_balance
                ")
                ->get();

            $data = $results->map(function ($item) {
                return [
                    'id' => $item->id,
                    'description' => $item->description,
                    'initial_budget' => (float) $item->initial_budget,
                    'planned_budget' => (float) $item->planned_budget,
                    'total_debit' => (float) $item->total_debit,
                    'total_credit' => (float) $item->total_credit,
                    'current_balance' => (float) $item->current_balance,
                ];
            })->keyBy('id')->toArray();

            return [
                'success' => true,
                'data' => $data,
            ];
        } catch (Exception $e) {
            Log::error('Failed to get bulk budget balances: '.$e->getMessage(), [
                'budget_item_ids' => $budgetItemIds,
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengambil data saldo anggaran: '.$e->getMessage(),
                'data' => [],
            ];
        }
    }

    private function validateSubmissionBudgetItem(WorkplanBudgetItem $budgetItem, int $workPlanId, string $role): array
    {
        if ((int) $budgetItem->kpi_workplan_id !== $workPlanId) {
            return [
                'success' => false,
                'message' => "Budget item {$role} tidak sesuai dengan workplan yang dipilih.",
            ];
        }

        if (! $budgetItem->isApproved()) {
            return [
                'success' => false,
                'message' => "Budget item {$role} belum approved.",
            ];
        }

        return ['success' => true, 'message' => 'OK'];
    }
}
