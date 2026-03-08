<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubmissionMainTemplateSheet implements FromArray, WithHeadings, WithTitle, WithStyles
{
    public function title(): string
    {
        return 'Worksheet';
    }

    public function headings(): array
    {
        return [
            'Ref No',
            'Transaction Date (YYYY-MM-DD)',
            'Planned Usage Date (YYYY-MM-DD)',
            'Program Name',
            'Purpose',
            'Urgency',
            'Item Name',
            'Budget Code',
            'Unit',
            'Quantity',
            'Price',
            'Remark'
        ];
    }

    public function array(): array
    {
        return [
            [
                '1',
                date('Y-m-d'),
                date('Y-m-d', strtotime('+7 days')),
                'Example Program (Activity)',
                'Kebutuhan Kantor',
                'Penting dan Mendesak',
                'Kertas A4',
                '501.01.01',
                'Rim',
                5,
                55000,
                'Untuk stok bulanan'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
