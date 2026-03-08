<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SubmissionImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // This will be handled in the Service layer
        return $rows;
    }
}
