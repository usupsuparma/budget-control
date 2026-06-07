<?php

namespace App\Services\BudgetResumeService;

interface BudgetResumeService
{
    /**
     * Build Budget Resume page data from the immutable budget ledger.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPageData(array $filters = []): array;
}
