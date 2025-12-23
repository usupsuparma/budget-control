<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MarketingPlanTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        $base = [
            'year',
            'customer',
            'customer_detail',
            'segment',
            'brand',
            'conc',
            'packing1',
            'packing2',
            'area1',
            'area2',
            'product_kg',
            'price',
            'increase_decrease_price'
        ];

        $monthly = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthly = array_merge($monthly, [
                "{$i}_diff",
                "{$i}_sales_budget",
                "{$i}_vc",
                "{$i}_fc",
                "{$i}_other_cost",
                "{$i}_trans",
                "{$i}_pack",
                "{$i}_total_cost",
                "{$i}_sales_price",
                "{$i}_margin",
                "{$i}_percent",
                "{$i}_sales_qty",
                "{$i}_sales_amount",
                "{$i}_margin_amount",
                "{$i}_full_cost",
                "{$i}_transport_amount",
                "{$i}_packing_amount",
            ]);
        }

        return array_merge($base, $monthly);
    }

    public function array(): array
    {
        // === 1 BARIS CONTOH ===
        $example = [
            '2025',                // year
            'Customer A',          // customer
            'Detail Customer',     // customer_detail
            'Retail',              // segment
            'Brand X',             // brand
            '50%',                 // conc
            'Bag',                 // packing1
            'Box',                 // packing2
            'Bandung',             // area1
            'Jakarta',             // area2
            12000,                 // product_kg
            18000,                 // price
            1000,                  // increase_decrease_price
        ];

        // Tambahkan contoh untuk 12 bulan × 17 kolom angka → isi default 0
        for ($i = 1; $i <= 12; $i++) {
            $example = array_merge($example, [
                1,
                1,
                1,
                1,
                1,
                1,
                1,
                1,
                1,
                1,
                1,
                1,
                1,
                1,
                1,
                1,
                1
            ]);
        }

        // Kembalikan array berisi 1 baris contoh
        return [
            $example
        ];
    }
}
