<?php

namespace App\Imports;

use App\Models\Production;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductionImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $type = trim((string)($row['type'] ?? ''));
            $productionName = trim((string)($row['production'] ?? ''));
            $year = $row['year'] ?? null;
            $detail = trim((string)($row['detail'] ?? ''));

            if ($type === '' || $productionName === '' || $detail === '') continue;

            if (!in_array($type, Production::TYPES, true)) {
                // skip / or throw exception
                continue;
            }

            $p = Production::firstOrCreate(
                ['type' => $type, 'production' => $productionName, 'year' => $year],
                ['type' => $type, 'production' => $productionName, 'year' => $year]
            );

            $months = [];
            foreach (['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'] as $m) {
                $months[$m] = (float)($row[$m] ?? 0);
            }
            $total = array_sum($months);

            $p->details()->updateOrCreate(
                ['detail' => $detail],
                array_merge($months, ['total' => $total])
            );
        }
    }
}
