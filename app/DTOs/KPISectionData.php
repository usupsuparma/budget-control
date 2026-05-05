<?php

namespace App\DTOs;

readonly class KPISectionData
{
    public function __construct(
        public int $year,
        public int $kpi_department_id,
        public int $section_id,
        public string $section_goals,
        public ?string $activities,
        public ?string $target_section,
        public ?int $duration_days,
        public ?string $schedule_start,
        public ?string $schedule_end,
        public bool $jan,
        public bool $feb,
        public bool $mar,
        public bool $apr,
        public bool $may,
        public bool $jun,
        public bool $jul,
        public bool $aug,
        public bool $sep,
        public bool $oct,
        public bool $nov,
        public bool $dec,
        public ?string $revenue_cost,
        public ?string $unit_id,
        public ?string $description,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            year: (int) $data['year'],
            kpi_department_id: (int) $data['kpi_department_id'],
            section_id: (int) $data['section_id'],
            section_goals: (string) $data['section_goals'],
            activities: $data['activities'] ?? null,
            target_section: $data['target_section'] ?? null,
            duration_days: isset($data['duration_days']) ? (int) $data['duration_days'] : null,
            schedule_start: $data['schedule_start'] ?? null,
            schedule_end: $data['schedule_end'] ?? null,
            jan: self::toBool($data['jan'] ?? false),
            feb: self::toBool($data['feb'] ?? false),
            mar: self::toBool($data['mar'] ?? false),
            apr: self::toBool($data['apr'] ?? false),
            may: self::toBool($data['may'] ?? false),
            jun: self::toBool($data['jun'] ?? false),
            jul: self::toBool($data['jul'] ?? false),
            aug: self::toBool($data['aug'] ?? false),
            sep: self::toBool($data['sep'] ?? false),
            oct: self::toBool($data['oct'] ?? false),
            nov: self::toBool($data['nov'] ?? false),
            dec: self::toBool($data['dec'] ?? false),
            revenue_cost: $data['revenue_cost'] ?? null,
            unit_id: $data['unit_id'] ?? null,
            description: $data['description'] ?? null,
        );
    }

    private static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null) {
            return false;
        }

        $value = strtolower((string) $value);

        return in_array($value, ['1', 'true', 'yes', 'y', 'ya', 'on'], true);
    }
}
