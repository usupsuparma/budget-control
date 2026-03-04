<?php

namespace App\Exports\Sheets;

use App\Models\KPIWorkPlan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReferenceProgramsSheet implements FromCollection, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Ref Programs';
    }

    public function headings(): array
    {
        return ['Program Name (Activity)', 'Year', 'KPI Type'];
    }

    public function collection()
    {
        return KPIWorkPlan::where('status', 'approved')
            ->orderBy('year', 'desc')
            ->orderBy('activity')
            ->get(['activity', 'year', 'kpi_type']);
    }
}
