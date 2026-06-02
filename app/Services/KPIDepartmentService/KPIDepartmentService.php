<?php

namespace App\Services\KPIDepartmentService;

use App\DTOs\KPIDepartmentData;
use App\Models\KPIDepartment;

interface KPIDepartmentService
{
    public function getIndexData(): array;

    public function getDataTableRows(?int $year): array;

    /**
     * Return KPI Divisions filtered by year (and by user's division if not admin).
     * Returns [{id, text}] for dropdown population.
     */
    public function getKpiDivisionsByYear(int $year): array;

    /**
     * Return Departments filtered by KPI Division ID (actually filters by the underlying division_id).
     * Returns [{id, text}] for dropdown population.
     */
    public function getDepartmentsByKpiDivision(int $kpiDivisionId): array;

    public function create(KPIDepartmentData $data): KPIDepartment;

    public function update(int $id, KPIDepartmentData $data): KPIDepartment;

    public function find(int $id): KPIDepartment;

    public function delete(int $id): void;
}
