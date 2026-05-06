<?php

namespace App\Services\KPIDepartmentService;

use App\DTOs\KPIDepartmentData;
use App\Models\KPIDepartement;

interface KPIDepartmentService
{
    public function getIndexData(): array;

    public function getDataTableRows(?int $year): array;

    public function create(KPIDepartmentData $data): KPIDepartement;

    public function update(int $id, KPIDepartmentData $data): KPIDepartement;

    public function find(int $id): KPIDepartement;

    public function delete(int $id): void;
}
