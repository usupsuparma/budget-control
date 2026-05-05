<?php

namespace App\Services\KPIDivisionService;

use App\DTOs\KPIDivisionData;
use App\Exceptions\DomainException;
use App\Exceptions\KPIDivisionNotFoundException;
use App\Models\CompanyPolicyDetail;
use App\Models\Division;
use App\Models\KPIDivision;
use Illuminate\Support\Facades\DB;

class KPIDivisionServiceImpl implements KPIDivisionService
{
    public function getIndexData(): array
    {
        $title = 'KPI Division';
        $companyPolicies = CompanyPolicyDetail::with('dokumen')
            ->orderBy('id', 'desc')
            ->get();

        $divisions = Division::orderBy('name')->get();

        $currentYear = now()->year;
        $kpiYears = KPIDivision::query()
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->map(fn($year) => (int) $year)
            ->toArray();

        if (! in_array($currentYear, $kpiYears, true)) {
            $kpiYears[] = $currentYear;
        }

        rsort($kpiYears);

        return compact('title', 'companyPolicies', 'divisions', 'kpiYears', 'currentYear');
    }

    public function getDataTableRows(?int $year): array
    {
        $filterYear = $year ?? now()->year;

        $kpis = KPIDivision::with(['companyPolicy', 'division'])
            ->where('year', $filterYear)
            ->orderBy('id', 'desc')
            ->get();

        $no = 1;
        $rows = [];

        foreach ($kpis as $kpi) {
            $rows[] = [
                'id' => $kpi->id,
                'no' => $no++,
                'year' => $kpi->year,
                'company_policy' => strip_tags(optional($kpi->companyPolicy)->strategic_goal ?? '-'),
                'division' => strip_tags(optional($kpi->division)->name ?? 'Division #' . $kpi->division_id),
                'division_goals' => $kpi->division_goals,
                'target_division' => $kpi->target_division,
                'duration_days' => $kpi->duration_days,
                'schedule_start' => optional($kpi->schedule_start)->format('Y-m-d'),
                'schedule_end' => optional($kpi->schedule_end)->format('Y-m-d'),
                'jan' => (bool) $kpi->jan,
                'feb' => (bool) $kpi->feb,
                'mar' => (bool) $kpi->mar,
                'apr' => (bool) $kpi->apr,
                'may' => (bool) $kpi->may,
                'jun' => (bool) $kpi->jun,
                'jul' => (bool) $kpi->jul,
                'aug' => (bool) $kpi->aug,
                'sep' => (bool) $kpi->sep,
                'oct' => (bool) $kpi->oct,
                'nov' => (bool) $kpi->nov,
                'dec' => (bool) $kpi->dec,
                'revenue_cost' => $kpi->revenue_cost,
                'pic' => $kpi->pic,
                'description' => $kpi->description,
            ];
        }

        return $rows;
    }

    public function create(KPIDivisionData $data): KPIDivision
    {
        return DB::transaction(function () use ($data) {
            return KPIDivision::create($this->payloadFromData($data));
        });
    }

    public function update(int $id, KPIDivisionData $data): KPIDivision
    {
        return DB::transaction(function () use ($id, $data) {
            $kpi = $this->find($id);
            $kpi->update($this->payloadFromData($data));

            return $kpi;
        });
    }

    public function find(int $id): KPIDivision
    {
        $kpi = KPIDivision::with(['companyPolicy', 'division'])->find($id);

        if (! $kpi) {
            throw new KPIDivisionNotFoundException();
        }

        return $kpi;
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $kpi = KPIDivision::find($id);

            if (! $kpi) {
                throw new KPIDivisionNotFoundException();
            }

            $kpi->delete();
        });
    }

    public function inlineUpdate(int $id, string $field, mixed $value): array
    {
        $allowed = [
            'year',
            'division_goals',
            'target_division',
            'duration_days',
            'schedule_start',
            'schedule_end',
            'jan',
            'feb',
            'mar',
            'apr',
            'may',
            'jun',
            'jul',
            'aug',
            'sep',
            'oct',
            'nov',
            'dec',
            'revenue_cost',
            'pic',
            'description',
        ];

        if (! in_array($field, $allowed, true)) {
            throw new DomainException('Field tidak boleh diubah inline.', 422);
        }

        $displayValue = $value;

        $kpi = DB::transaction(function () use ($id, $field, $value) {
            $kpi = KPIDivision::find($id);

            if (! $kpi) {
                throw new KPIDivisionNotFoundException();
            }

            if ($field === 'year') {
                $kpi->year = (int) $value;
            } elseif ($field === 'duration_days') {
                $kpi->duration_days = $value !== null ? (int) $value : null;
            } elseif (in_array($field, ['schedule_start', 'schedule_end'], true)) {
                $kpi->{$field} = $value ?: null;
            } elseif ($this->isMonthField($field)) {
                $kpi->{$field} = $this->toBool($value);
            } else {
                $kpi->{$field} = $value;
            }

            $kpi->save();

            return $kpi;
        });

        if ($this->isMonthField($field)) {
            $displayValue = $kpi->{$field} ? 'Yes' : 'No';
        }

        return [
            'model' => $kpi,
            'display_value' => (string) $displayValue,
            'value' => $kpi->{$field},
        ];
    }

    private function payloadFromData(KPIDivisionData $data): array
    {
        return [
            'company_policy_detail_id' => $data->company_policy_detail_id,
            'division_id' => $data->division_id,
            'year' => $data->year,
            'division_goals' => $data->division_goals,
            'target_division' => $data->target_division,
            'duration_days' => $data->duration_days,
            'schedule_start' => $data->schedule_start,
            'schedule_end' => $data->schedule_end,
            'jan' => $data->jan,
            'feb' => $data->feb,
            'mar' => $data->mar,
            'apr' => $data->apr,
            'may' => $data->may,
            'jun' => $data->jun,
            'jul' => $data->jul,
            'aug' => $data->aug,
            'sep' => $data->sep,
            'oct' => $data->oct,
            'nov' => $data->nov,
            'dec' => $data->dec,
            'revenue_cost' => $data->revenue_cost,
            'pic' => $data->pic,
            'description' => $data->description,
        ];
    }

    private function isMonthField(string $field): bool
    {
        return in_array($field, ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'], true);
    }

    private function toBool(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower((string) $value);

        return in_array($value, ['1', 'true', 'yes', 'y', 'ya', 'on'], true);
    }
}
