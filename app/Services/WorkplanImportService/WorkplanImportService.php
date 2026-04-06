<?php

namespace App\Services\WorkplanImportService;

use Illuminate\Http\UploadedFile;

interface WorkplanImportService
{
    /**
     * Import workplans and budget items from CSV.
     *
     * @param UploadedFile $file
     * @return array Summary of import processing (e.g. processed, skipped, failed)
     * @throws \App\Exceptions\DomainException
     */
    public function importWorkplanBudget(UploadedFile $file): array;
}
