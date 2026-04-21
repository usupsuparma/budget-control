<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * MacframeImport
 *
 * Reads a MacframeGA Excel file WITHOUT heading row processing,
 * because the file has a two-section structure:
 *   Row 1 : Master header
 *   Row 2 : Master data
 *   Row 3 : Detail header
 *   Row 4+ : Detail rows
 *
 * Parsing is fully delegated to SubmissionServiceImpl::parseMacframeFile().
 */
class MacframeImport implements ToCollection
{
    public function collection(Collection $rows): Collection
    {
        // Raw rows returned as-is; service layer handles the parsing.
        return $rows;
    }
}
