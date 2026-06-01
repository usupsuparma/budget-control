<?php

namespace App\Services\KPISectionService;

use App\DTOs\KPISectionData;
use App\Exceptions\KPISectionNotFoundException;
use App\Models\KPIDepartment;
use App\Models\KPISection;
use App\Models\Section;
use App\Services\UserRoleService\UserRoleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KPISectionServiceImpl implements KPISectionService
{
    public function __construct(private UserRoleService $userRoleService) {}

    public function getIndexData(): array
    {
        $title = 'KPI Section';
        $user = Auth::user();
        $isAdmin = $this->userRoleService->isAdmin($user);
        $divisionIds = $isAdmin ? [] : $this->userRoleService->getDivisionIds($user);

        $kpiDeptQuery = KPIDepartment::orderBy('id', 'desc');
        if (! $isAdmin) {
            $kpiDeptQuery->whereHas('kpiDivision', fn($q) => $q->whereIn('division_id', $divisionIds));
        }
        $kpiDepartments = $kpiDeptQuery->get();

        $sections = Section::orderBy('name')->get();

        $currentYear = now()->year;
        $kpiYears = KPISection::query()
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

        return compact('title', 'kpiDepartments', 'sections', 'kpiYears', 'currentYear', 'isAdmin');
    }

    public function getDataTableRows(?int $year): array
    {
        $filterYear = $year ?? now()->year;
        $user = Auth::user();
        $isAdmin = $this->userRoleService->isAdmin($user);
        $divisionIds = $isAdmin ? [] : $this->userRoleService->getDivisionIds($user);

        $query = KPISection::with(['KPIDepartment', 'section'])
            ->where('year', $filterYear);

        if (! $isAdmin) {
            $query->whereHas('KPIDepartment.kpiDivision', fn($q) => $q->whereIn('division_id', $divisionIds));
        }

        $rows = $query->orderBy('id', 'desc')->get();

        $data = [];

        foreach ($rows as $i => $kpi) {
            $monthKeys = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

            $months = [];
            foreach ($monthKeys as $m) {
                $months[$m] = [
                    'value' => (int) $kpi->{$m},
                    'label' => $kpi->{$m} ? 'Yes' : 'No',
                ];
            }

            $data[] = [
                'no' => $i + 1,
                'id' => $kpi->id,
                'year' => $kpi->year,
                'kpi_department_id' => $kpi->kpi_department_id,
                'kpi_department' => optional($kpi->KPIDepartment)->department_goals ?? '-',
                'section_id' => $kpi->section_id,
                'section' => optional($kpi->section)->name ?? '-',
                'section_goals' => $kpi->section_goals,
                'activities' => $kpi->activities,
                'target_section' => $kpi->target_section,
                'duration_days' => $kpi->duration_days,
                'schedule_start' => optional($kpi->schedule_start)->format('Y-m-d'),
                'schedule_end' => optional($kpi->schedule_end)->format('Y-m-d'),
                ...$months,
                'revenue_cost' => $kpi->revenue_cost,
                'unit_id' => $kpi->unit_id,
                'description' => $kpi->description,
            ];
        }

        return $data;
    }

    public function getKpiDepartmentsByYear(int $year): array
    {
        $user = Auth::user();
        $isAdmin = $this->userRoleService->isAdmin($user);
        $divisionIds = $isAdmin ? [] : $this->userRoleService->getDivisionIds($user);

        $query = KPIDepartment::where('year', $year)
            ->orderBy('department_goals');

        if (! $isAdmin) {
            $query->whereHas('kpiDivision', fn($q) => $q->whereIn('division_id', $divisionIds));
        }

        return $query->get()->map(fn($dept) => [
            'id'   => $dept->id,
            'text' => '[' . $dept->year . '] ' . Str::limit($dept->department_goals, 80),
        ])->toArray();
    }

    public function getSectionsByKpiDepartment(int $kpiDepartmentId): array
    {
        $kpiDept = KPIDepartment::find($kpiDepartmentId);

        if (! $kpiDept) {
            return [];
        }

        return Section::where('department_id', $kpiDept->department_id)
            ->orderBy('name')
            ->get()
            ->map(fn($sec) => [
                'id'   => $sec->id,
                'text' => $sec->name,
            ])->toArray();
    }

    public function create(KPISectionData $data): KPISection
    {
        return DB::transaction(function () use ($data) {
            return KPISection::create($this->payloadFromData($data));
        });
    }

    public function update(int $id, KPISectionData $data): KPISection
    {
        return DB::transaction(function () use ($id, $data) {
            $kpi = $this->find($id);
            $kpi->update($this->payloadFromData($data));

            return $kpi;
        });
    }

    public function find(int $id): KPISection
    {
        $kpi = KPISection::with(['KPIDepartment', 'section'])->find($id);

        if (! $kpi) {
            throw new KPISectionNotFoundException();
        }

        return $kpi;
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $kpi = KPISection::find($id);

            if (! $kpi) {
                throw new KPISectionNotFoundException();
            }

            $kpi->delete();
        });
    }

    private function payloadFromData(KPISectionData $data): array
    {
        return [
            'year' => $data->year,
            'kpi_department_id' => $data->kpi_department_id,
            'section_id' => $data->section_id,
            'section_goals' => $data->section_goals,
            'activities' => $data->activities,
            'target_section' => $data->target_section,
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
            'unit_id' => $data->unit_id,
            'description' => $data->description,
        ];
    }
}
