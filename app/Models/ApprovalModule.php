<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalModule extends Model
{
    /**
     * Daftar tabel yang diizinkan untuk sistem approval.
     * Key = nama tabel database, Value = label yang ditampilkan ke user.
     */
    public const ALLOWED_TABLES = [
        'transactions' => 'Transactions',
        'budget_submissions' => 'Budget Submissions',
        'company_policies' => 'Company Policies',
        'marketing_plans' => 'Marketing Plans',
        'sales_plannings' => 'Sales Plannings',
        'workplan_budget_items' => 'Workplan Budget Items',
        'kpi_workplans' => 'KPI Workplans',
    ];

    protected $table = "approval_modules";
    protected $fillable = [
        "module_name",
        "table_name",
        "condition_field",
        "is_active",
    ];

    protected $casts = [
        "is_active" => "boolean",
    ];

    /**
     * Get list of tables that are still available (not yet used by any module).
     */
    public static function getAvailableTables(): array
    {
        $usedTables = self::pluck('table_name')->toArray();
        return array_diff_key(self::ALLOWED_TABLES, array_flip($usedTables));
    }

    /**
     * Get list of tables available for editing a specific module.
     * Includes the current module's table + tables not used by other modules.
     */
    public static function getAvailableTablesForEdit(int $excludeId): array
    {
        $usedTables = self::where('id', '!=', $excludeId)->pluck('table_name')->toArray();
        return array_diff_key(self::ALLOWED_TABLES, array_flip($usedTables));
    }

    public function templates()
    {
        return $this->hasMany(ApprovalFlowTemplate::class, 'module_id');
    }

    
}
