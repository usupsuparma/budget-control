<?php

namespace App\Exports\Sheets;

use App\Models\Unit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReferenceUnitsSheet implements FromCollection, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Ref Units';
    }

    public function headings(): array
    {
        return ['Unit Name', 'Code'];
    }

    public function collection()
    {
        return Unit::orderBy('unit')->get(['unit', 'code']);
    }
}
