<?php

namespace App\Services\KPIDivisionService;

use App\DTOs\KPIDivisionData;
use App\Models\KPIDivision;

interface KPIDivisionService
{
    public function getIndexData(): array;

    public function getDataTableRows(?int $year): array;

    public function create(KPIDivisionData $data): KPIDivision;

    public function update(int $id, KPIDivisionData $data): KPIDivision;

    public function find(int $id): KPIDivision;

    public function delete(int $id): void;

    /**
     * @return array{model: KPIDivision, display_value: string, value: mixed}
     */
    public function inlineUpdate(int $id, string $field, mixed $value): array;
}
