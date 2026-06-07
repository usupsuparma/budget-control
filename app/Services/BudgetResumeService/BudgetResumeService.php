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

    /**
     * Search active budget codes with server-side paging.
     *
     * @return array{success: bool, data: mixed, has_more: bool, page: int, total: int}
     */
    public function searchBudgetCodes(string $query, int $limit = 10, int $page = 1): array;

    /**
     * Get one active budget code by exact code for filter preselection.
     *
     * @return array{success: bool, data: mixed}
     */
    public function getBudgetCodeByCode(string $code): array;
}
