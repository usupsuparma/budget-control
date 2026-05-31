<?php

namespace App\Services\KPIDepartmentService;

use App\DTOs\KPIDepartmentData;
use App\Exceptions\KPIDepartmentNotFoundException;
use App\Models\Department;
use App\Models\KPIDivision;
use App\Models\KPIDepartment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KPIDepartmentServiceImpl implements KPIDepartmentService
{
    public function getIndexData(): array
    {
        $title = 'KPI Department';
        $user = Auth::user();
        $isAdmin = $this->isAdmin($user);
        $divisionIds = $isAdmin ? [] : $this->getDivisionIds($user);

        $kpiDivisionQuery = KPIDivision::query()
            ->orderBy('year')
            ->orderBy('division_goals');

        $departmentQuery = Department::query()->orderBy('name');

        if (! $isAdmin) {
            $kpiDivisionQuery->whereIn('division_id', $divisionIds);
            $departmentQuery->whereIn('division_id', $divisionIds);
        }

        $kpiDivisions = $kpiDivisionQuery->get();
        $departments = $departmentQuery->get();

        $currentYear = now()->year;
        $kpiYears = KPIDepartment::query()
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

        return compact('title', 'kpiDivisions', 'departments', 'kpiYears', 'currentYear');
    }

    public function getDataTableRows(?int $year): array
    {
        $filterYear = $year ?? now()->year;
        $user = Auth::user();
        $isAdmin = $this->isAdmin($user);
        $divisionIds = $isAdmin ? [] : $this->getDivisionIds($user);

        $query = KPIDepartment::with(['kpiDivision', 'department'])
            ->where('year', $filterYear);

        if (! $isAdmin) {
            $query->whereHas('kpiDivision', function ($q) use ($divisionIds) {
                $q->whereIn('division_id', $divisionIds);
            });
        }

        $items = $query->orderBy('id', 'desc')->get();

        $rows = [];
        $no = 1;

        foreach ($items as $row) {
            $rows[] = [
                'id' => $row->id,
                'no' => $no++,
                'year' => $row->year,
                'kpi_division' => optional($row->kpiDivision)->division_goals ?? '-',
                'kpi_division_id' => $row->kpi_division_id,
                'department' => optional($row->department)->name ?? '-',
                'department_id' => $row->department_id,
                'department_goals' => $row->department_goals,
                'department_activities' => $row->department_activities,
                'target_department' => $row->target_department,
                'duration_days' => $row->duration_days,
                'schedule_start' => optional($row->schedule_start)->format('Y-m-d'),
                'schedule_end' => optional($row->schedule_end)->format('Y-m-d'),
                'jan' => (bool) $row->jan,
                'feb' => (bool) $row->feb,
                'mar' => (bool) $row->mar,
                'apr' => (bool) $row->apr,
                'may' => (bool) $row->may,
                'jun' => (bool) $row->jun,
                'jul' => (bool) $row->jul,
                'aug' => (bool) $row->aug,
                'sep' => (bool) $row->sep,
                'oct' => (bool) $row->oct,
                'nov' => (bool) $row->nov,
                'dec' => (bool) $row->dec,
                'revenue_cost' => $row->revenue_cost,
                'pic' => $row->pic,
                'description' => $row->description,
            ];
        }

        return $rows;
    }

    public function create(KPIDepartmentData $data): KPIDepartment
    {
        return DB::transaction(function () use ($data) {
            return KPIDepartment::create($this->payloadFromData($data));
        });
    }

    public function update(int $id, KPIDepartmentData $data): KPIDepartment
    {
        return DB::transaction(function () use ($id, $data) {
            $kpiDept = $this->find($id);
            $kpiDept->update($this->payloadFromData($data));

            return $kpiDept;
        });
    }

    public function find(int $id): KPIDepartment
    {
        $kpiDept = KPIDepartment::with(['kpiDivision', 'department'])->find($id);

        if (! $kpiDept) {
            throw new KPIDepartmentNotFoundException();
        }

        return $kpiDept;
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $kpiDept = KPIDepartment::find($id);

            if (! $kpiDept) {
                throw new KPIDepartmentNotFoundException();
            }

            $kpiDept->delete();
        });
    }

    private function payloadFromData(KPIDepartmentData $data): array
    {
        return [
            'year' => $data->year,
            'kpi_division_id' => $data->kpi_division_id,
            'department_id' => $data->department_id,
            'department_goals' => $data->department_goals,
            'department_activities' => $data->department_activities,
            'target_department' => $data->target_department,
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

    private function isAdmin($user): bool
    {
        if (! $user) {
            return true;
        }

        return $user->hasRole('Admin') || $user->hasRole('admin') || $user->hasRole('super-admin');
    }

    private function getDivisionIds($user): array
    {
        $employment = $user?->employment;

        if (! $employment) {
            return [];
        }

        return $employment->getDivisionIds();
    }
}
