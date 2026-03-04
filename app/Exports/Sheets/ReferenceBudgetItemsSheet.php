<?php

namespace App\Exports\Sheets;

use App\Models\WorkplanBudgetItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReferenceBudgetItemsSheet implements FromCollection, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Ref Budget Items';
    }

    public function headings(): array
    {
        return ['Program Name (Activity)', 'Budget Code', 'Description', 'Category'];
    }

    public function collection()
    {
        return WorkplanBudgetItem::with(['workplan', 'category'])
            ->where('status', 'approved')
            ->get()
            ->map(function ($item) {
                return [
                    'program_name' => $item->workplan->activity ?? 'N/A',
                    'budget_code' => $item->budget_code,
                    'description' => $item->description,
                    'category' => $item->category->category_name ?? 'N/A',
                ];
            });
    }
}
