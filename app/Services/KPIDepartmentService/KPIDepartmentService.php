<?php

namespace App\Services\KPIDepartmentService;

use App\DTOs\KPIDepartmentData;
use App\Models\KPIDepartment;

interface KPIDepartmentService
{
    public function getIndexData(): array;

    public function getDataTableRows(?int $year): array;

    public function create(KPIDepartmentData $data): KPIDepartment;

    public function update(int $id, KPIDepartmentData $data): KPIDepartment;

    public function find(int $id): KPIDepartment;

    public function delete(int $id): void;
}
