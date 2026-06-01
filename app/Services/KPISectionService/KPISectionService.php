<?php

namespace App\Services\KPISectionService;

use App\DTOs\KPISectionData;
use App\Models\KPISection;

interface KPISectionService
{
    public function getIndexData(): array;

    public function getDataTableRows(?int $year): array;

    /**
     * Return KPI Departments filtered by year (and by user's division if not admin).
     * Returns [{id, text}] for dropdown population.
     */
    public function getKpiDepartmentsByYear(int $year): array;

    /**
     * Return Sections whose department matches the given KPI Department's department_id.
     * Returns [{id, text}] for dropdown population.
     */
    public function getSectionsByKpiDepartment(int $kpiDepartmentId): array;

    public function create(KPISectionData $data): KPISection;

    public function update(int $id, KPISectionData $data): KPISection;

    public function find(int $id): KPISection;

    public function delete(int $id): void;
}
