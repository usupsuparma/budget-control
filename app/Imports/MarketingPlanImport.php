<?php

namespace App\Imports;

use App\Models\MarketingPlan;
use App\Models\SalesPlanning;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MarketingPlanImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Jika baris kosong, skip
        if (!isset($row['year']) || $row['year'] == null) {
            return null;
        }

        return new MarketingPlan([
            'year' => $row['year'],
            'customer' => $row['customer'],
            'customer_detail' => $row['customer_detail'],
            'segment' => $row['segment'],
            'brand' => $row['brand'],
            'conc' => $row['conc'],
            'packing1' => $row['packing1'],
            'packing2' => $row['packing2'],
            'area1' => $row['area1'],
            'area2' => $row['area2'],
            'product_kg' => $row['product_kg'],
            'price' => $row['price'],
            'increase_decrease_price' => $row['increase_decrease_price'],
        ] + $this->monthlyMapping($row));
    }

    private function monthlyMapping($row)
    {
        $fields = [];

        for ($i = 1; $i <= 12; $i++) {
            $fields["{$i}_diff"] = $row["{$i}_diff"] ?? 0;
            $fields["{$i}_sales_budget"] = $row["{$i}_sales_budget"] ?? 0;
            $fields["{$i}_vc"] = $row["{$i}_vc"] ?? 0;
            $fields["{$i}_fc"] = $row["{$i}_fc"] ?? 0;
            $fields["{$i}_other_cost"] = $row["{$i}_other_cost"] ?? 0;
            $fields["{$i}_trans"] = $row["{$i}_trans"] ?? 0;
            $fields["{$i}_pack"] = $row["{$i}_pack"] ?? 0;
            $fields["{$i}_total_cost"] = $row["{$i}_total_cost"] ?? 0;
            $fields["{$i}_sales_price"] = $row["{$i}_sales_price"] ?? 0;
            $fields["{$i}_margin"] = $row["{$i}_margin"] ?? 0;
            $fields["{$i}_percent"] = $row["{$i}_percent"] ?? 0;
            $fields["{$i}_sales_qty"] = $row["{$i}_sales_qty"] ?? 0;
            $fields["{$i}_sales_amount"] = $row["{$i}_sales_amount"] ?? 0;
            $fields["{$i}_margin_amount"] = $row["{$i}_margin_amount"] ?? 0;
            $fields["{$i}_full_cost"] = $row["{$i}_full_cost"] ?? 0;
            $fields["{$i}_transport_amount"] = $row["{$i}_transport_amount"] ?? 0;
            $fields["{$i}_packing_amount"] = $row["{$i}_packing_amount"] ?? 0;
        }

        return $fields;
    }
}
