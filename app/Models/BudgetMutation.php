<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetMutation extends Model
{
    /**
     * Budget Mutation (Buku Besar Anggaran)
     * 
     * Immutable ledger entries for tracking budget usage.
     * Once inserted, records should NEVER be updated or deleted.
     * Corrections should be made via new adjustment entries.
     */

    protected $table = 'budget_mutations';

    // No updated_at column - ledger entries are immutable
    public $timestamps = false;

    protected $fillable = [
        'workplan_budget_item_id',
        'transaction_id',
        'transaction_detail_id',
        'transaction_lpj_submission_id',
        'mutation_type',
        'amount',
        'category',
        'description',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'created_at' => 'datetime',
    ];

    // Mutation type constants
    const TYPE_DEBIT = 'D';   // Keluar (pemotongan anggaran)
    const TYPE_CREDIT = 'C';  // Masuk (pengembalian anggaran)

    // Category constants
    const CATEGORY_CASH_ADVANCE = 'CASH_ADVANCE';
    const CATEGORY_LPJ_REFUND = 'LPJ_REFUND';
    const CATEGORY_LPJ_REIMBURSE = 'LPJ_REIMBURSE';

    // ========== RELATIONSHIPS ==========

    public function workplanBudgetItem()
    {
        return $this->belongsTo(WorkplanBudgetItem::class, 'workplan_budget_item_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function transactionDetail()
    {
        return $this->belongsTo(TransactionDetail::class, 'transaction_detail_id');
    }

    public function lpjSubmission()
    {
        return $this->belongsTo(TransactionLpjSubmission::class, 'transaction_lpj_submission_id');
    }

    // ========== SCOPES ==========

    public function scopeDebit($query)
    {
        return $query->where('mutation_type', self::TYPE_DEBIT);
    }

    public function scopeCredit($query)
    {
        return $query->where('mutation_type', self::TYPE_CREDIT);
    }

    public function scopeByBudgetItem($query, int $budgetItemId)
    {
        return $query->where('workplan_budget_item_id', $budgetItemId);
    }

    public function scopeByTransaction($query, int $transactionId)
    {
        return $query->where('transaction_id', $transactionId);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // ========== HELPERS ==========

    public function isDebit(): bool
    {
        return $this->mutation_type === self::TYPE_DEBIT;
    }

    public function isCredit(): bool
    {
        return $this->mutation_type === self::TYPE_CREDIT;
    }
}
