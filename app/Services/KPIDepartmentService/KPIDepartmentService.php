<?php

namespace App\Services\KPIDepartmentService;

use App\DTOs\KPIDepartmentData;
use App\Models\KpiDepartment;

interface KPIDepartmentService
{
    public function getIndexData(): array;

    public function getDataTableRows(?int $year): array;

    public function create(KPIDepartmentData $data): KpiDepartment;

    public function update(int $id, KPIDepartmentData $data): KpiDepartment;

    public function find(int $id): KpiDepartment;

    public function delete(int $id): void;
}
