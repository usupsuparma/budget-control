<?php 

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductionTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['type','production','year','detail','jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
    }

    public function array(): array
    {
        return [
            [
                'MAXIMUM PRODUCTION AMOUNT',
                'Production A',
                2026,
                'Detail 1',
                0,0,0,0,0,0,0,0,0,0,0,0
            ],
            [
                'PRODUCTION AND SALES BALANCE',
                'Production A',
                2026,
                'Detail 2',
                1.25, 2.00, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
            ],
        ];
    }
}
