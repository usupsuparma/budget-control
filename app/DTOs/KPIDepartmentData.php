<?php

namespace App\DTOs;

readonly class KPIDepartmentData
{
    public function __construct(
        public int $year,
        public int $kpi_division_id,
        public int $department_id,
        public string $department_goals,
        public ?string $department_activities,
        public ?string $target_department,
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
        public ?string $pic,
        public ?string $description,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            year: (int) $data['year'],
            kpi_division_id: (int) $data['kpi_division_id'],
            department_id: (int) $data['department_id'],
            department_goals: (string) $data['department_goals'],
            department_activities: $data['department_activities'] ?? null,
            target_department: $data['target_department'] ?? null,
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
            pic: $data['pic'] ?? null,
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
