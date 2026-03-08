<?php

namespace App\Exports;

use App\Exports\Sheets\SubmissionMainTemplateSheet;
use App\Exports\Sheets\ReferenceProgramsSheet;
use App\Exports\Sheets\ReferenceBudgetItemsSheet;
use App\Exports\Sheets\ReferenceUnitsSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SubmissionTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new SubmissionMainTemplateSheet(),
            new ReferenceProgramsSheet(),
            new ReferenceBudgetItemsSheet(),
            new ReferenceUnitsSheet(),
        ];
    }
}
