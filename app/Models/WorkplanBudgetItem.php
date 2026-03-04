<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkplanBudgetItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kpi_workplan_id',
        'budget_category_id',
        'description',
        'stock_code',
        'budget_code',
        'product_line',
        'cost_center',
        'beg_balance',
        'supplier_id',
        'supplier_name',
        'unit_id',
        'unit_name',
        'cons_rate',
        'unit',
        'total',
        'activity_jan',
        'activity_feb',
        'activity_mar',
        'activity_apr',
        'activity_may',
        'activity_jun',
        'activity_jul',
        'activity_aug',
        'activity_sep',
        'activity_oct',
        'activity_nov',
        'activity_dec',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'notes',
        'sort_order',
        'category_type', // Enum ['Routine', 'Carry Over', 'Turn Around', 'Multi Year']
        'price_estimation',
        'price_estimation_description',
        'verification_status', // Enum ['unverified', 'pending', 'verified', 'rejected']
        'price_final',

    ];

    protected $casts = [
        'total' => 'decimal:2',
        'activity_jan' => 'integer',
        'activity_feb' => 'integer',
        'activity_mar' => 'integer',
        'activity_apr' => 'integer',
        'activity_may' => 'integer',
        'activity_jun' => 'integer',
        'activity_jul' => 'integer',
        'activity_aug' => 'integer',
        'activity_sep' => 'integer',
        'activity_oct' => 'integer',
        'activity_nov' => 'integer',
        'activity_dec' => 'integer',
        'sort_order' => 'integer',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function workplan()
    {
        return $this->belongsTo(KpiWorkplan::class, 'kpi_workplan_id');
    }

    public function category()
    {
        return $this->belongsTo(BudgetCategory::class, 'budget_category_id');
    }

    public function budgetCodeRelation()
    {
        // WorkplanBudgetItem.budget_code → BudgetCode.budget_code (identifier column)
        return $this->belongsTo(BudgetCode::class, 'budget_code', 'budget_code');
    }

    public function stockCodeRelation()
    {
        // WorkplanBudgetItem.stock_code → StockCode.stock_code
        return $this->belongsTo(StockCode::class, 'stock_code', 'stock_code');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function approvalRequest()
    {
        return $this->hasOne(ApprovalRequest::class, 'reference_id')
            ->whereHas('module', fn($q) => $q->where('table_name', 'workplan_budget_items'));
    }

    /**
     * Get verification candidates (snapshot of who can verify this item)
     */
    public function verificationCandidates()
    {
        return $this->hasMany(WorkplanBudgetApprover::class, 'workplan_budget_item_id');
    }

    /**
     * Get verification history/audit trail
     */
    public function verifications()
    {
        return $this->hasMany(WorkplanBudgetVerification::class, 'workplan_budget_item_id');
    }

    /**
     * Get the executor (verifier who actually verified this item)
     */
    public function executor()
    {
        return $this->hasOne(WorkplanBudgetApprover::class, 'workplan_budget_item_id')
            ->where('is_executor', true);
    }

    // Scopes
    public function scopeByWorkplan($query, $workplanId)
    {
        return $query->where('kpi_workplan_id', $workplanId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('budget_category_id', $categoryId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Helper Methods
    public function getActivityMonths(): array
    {
        $months = [];
        $monthNames = [
            'jan' => 'January',
            'feb' => 'February',
            'mar' => 'March',
            'apr' => 'April',
            'may' => 'May',
            'jun' => 'June',
            'jul' => 'July',
            'aug' => 'August',
            'sep' => 'September',
            'oct' => 'October',
            'nov' => 'November',
            'dec' => 'December',
        ];

        foreach ($monthNames as $key => $name) {
            $months[$key] = [
                'name' => $name,
                'active' => $this->{"activity_$key"} > 0,
                'quantity' => $this->{"activity_$key"},
            ];
        }

        return $months;
    }

    public function getActiveMonthsCount(): int
    {
        $count = 0;
        foreach (
            [
                'jan',
                'feb',
                'mar',
                'apr',
                'may',
                'jun',
                'jul',
                'aug',
                'sep',
                'oct',
                'nov',
                'dec'
            ] as $month
        ) {
            if ($this->{"activity_$month"} > 0) {
                $count++;
            }
        }
        return $count;
    }

    public function getTotalActivityQuantity(): int
    {
        $total = 0;
        foreach (
            [
                'jan',
                'feb',
                'mar',
                'apr',
                'may',
                'jun',
                'jul',
                'aug',
                'sep',
                'oct',
                'nov',
                'dec'
            ] as $month
        ) {
            $total += $this->{"activity_$month"};
        }
        return $total;
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'rejected']);
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get the division_id associated with this budget item.
     * 
     * Path: WorkplanBudgetItem -> KpiWorkplan -> KpiDepartment/KpiSection -> KpiDivision -> Division
     * 
     * @return int|null
     */
    public function getDivisionId(): ?int
    {
        $workplan = $this->workplan;
        if (!$workplan) {
            return null;
        }

        if ($workplan->kpi_type === 'department') {
            $kpiDepartment = $workplan->kpiDepartment;
            if ($kpiDepartment && $kpiDepartment->kpiDivision && $kpiDepartment->kpiDivision->division) {
                return $kpiDepartment->kpiDivision->division_id;
            }
        } elseif ($workplan->kpi_type === 'section') {
            $kpiSection = $workplan->kpiSection;
            if ($kpiSection && $kpiSection->kpiDepartment && $kpiSection->kpiDepartment->kpiDivision && $kpiSection->kpiDepartment->kpiDivision->division) {
                return $kpiSection->kpiDepartment->kpiDivision->division_id;
            }
        }

        return null;
    }
}
