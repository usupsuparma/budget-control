<?php

namespace App\Services\MasterDataService;

use App\Models\Director;
use App\Models\Employment;
use App\Models\Division;
use App\Models\Department;
use App\Models\Section;
use App\Models\JobLevel;
use App\Models\JobPosition;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class MasterDataServiceImpl implements MasterDataService
{
    /**
     * Cache key for the organization tree.
     * Invalidate this key whenever Director/Division/Department/Section master data changes.
     */
    private const CACHE_KEY = 'org_tree_active';

    /**
     * Cache key for options.
     */
    private const CACHE_OPTIONS_KEY = 'master_options_active';

    /**
     * Cache TTL in seconds (6 hours). Org structure is master data that rarely changes.
     */
    private const CACHE_TTL = 60 * 60 * 6;

    /**
     * Eager-load full organizational tree with only active records at each level.
     *
     * Performance improvements applied (per PR review):
     * 1. Column selection: only fetch columns needed for the org chart view,
     *    minimizing memory footprint on large organizations.
     * 2. Strategic caching: the result is cached for 6 hours since org structure
     *    is master data that rarely changes. Call forgetCache() after any CRUD
     *    on Director/Division/Department/Section to invalidate.
     *
     * Hierarchy: Director → Division → Department → Section
     *
     * @return Collection<Director>
     */
    public function getOrganizationTree(): Collection
    {
        $directors = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Director::where('status', 'Active')
                ->select(['id', 'name', 'code', 'status'])
                ->with([
                    'divisions' => function ($q) {
                        $q->where('status', 'Active')
                            ->orderBy('name')
                            ->select(['id', 'director_id', 'name', 'status']);
                    },
                    'divisions.departments' => function ($q) {
                        $q->where('status', 'Active')
                            ->orderBy('name')
                            ->select(['id', 'division_id', 'name', 'code', 'status']);
                    },
                    'divisions.departments.sections' => function ($q) {
                        $q->where('status', 'Active')
                            ->orderBy('name')
                            ->select(['id', 'department_id', 'name', 'code', 'status']);
                    },
                ])
                ->orderBy('name', 'asc')
                ->get();
        });

        return $this->attachHeadNames($directors);
    }

    /**
     * Get all master data options for dropdowns.
     */
    public function getAllOptions(): array
    {
        return Cache::remember(self::CACHE_OPTIONS_KEY, self::CACHE_TTL, function () {
            return [
                'job_positions' => JobPosition::where('status', 'Active')->orderBy('name')->select(['id', 'name'])->get(),
                'job_levels'    => JobLevel::where('status', 'Active')->orderBy('name')->select(['id', 'name'])->get(),
                'directors'     => Director::where('status', 'Active')->orderBy('name')->select(['id', 'name'])->get(),
                'divisions'     => Division::where('status', 'Active')->orderBy('name')->select(['id', 'name', 'director_id'])->get(),
                'departments'   => Department::where('status', 'Active')->orderBy('name')->select(['id', 'name', 'division_id'])->get(),
                'sections'      => Section::where('status', 'Active')->orderBy('name')->select(['id', 'name', 'department_id'])->get(),
            ];
        });
    }


    /**
     * Invalidate the cached organization tree.
     *
     * Call this method from any Service that mutates Director, Division,
     * Department, or Section master data to ensure the cache stays fresh.
     *
     * Example usage:
     *   app(MasterDataService::class)->forgetCache();
     */
    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_OPTIONS_KEY);
    }

    private function attachHeadNames(Collection $directors): Collection
    {
        $employments = Employment::with(['employee', 'jobPosition'])
            ->where('status', 'Active')
            ->whereNotNull('job_position_id')
            ->get();

        $heads = [];
        foreach ($employments as $employment) {
            $job = $employment->jobPosition;
            $employee = $employment->employee;
            if (! $job || ! $employee) {
                continue;
            }

            $structureId = (int) $job->structure_id;
            $levelId = (int) $job->job_level_id;
            if (! $structureId || ! $levelId) {
                continue;
            }

            $keyLevel = $levelId >= 4 ? 4 : $levelId;
            $key = sprintf('%s:%s', $keyLevel, $structureId);

            if (! isset($heads[$key])) {
                $heads[$key] = [
                    'employee_name' => $employee->name,
                    'job_position_name' => $job->job_position_name,
                ];
            }
        }

        foreach ($directors as $director) {
            $directorKey = sprintf('1:%s', $director->id);
            $directorHead = $heads[$directorKey] ?? null;
            $director->head_employee_name = $directorHead['employee_name'] ?? null;
            $director->head_job_position = $directorHead['job_position_name'] ?? null;

            foreach ($director->divisions as $division) {
                $divisionKey = sprintf('2:%s', $division->id);
                $divisionHead = $heads[$divisionKey] ?? null;
                $division->head_employee_name = $divisionHead['employee_name'] ?? null;
                $division->head_job_position = $divisionHead['job_position_name'] ?? null;

                foreach ($division->departments as $department) {
                    $departmentKey = sprintf('3:%s', $department->id);
                    $departmentHead = $heads[$departmentKey] ?? null;
                    $department->head_employee_name = $departmentHead['employee_name'] ?? null;
                    $department->head_job_position = $departmentHead['job_position_name'] ?? null;

                    foreach ($department->sections as $section) {
                        $sectionKey = sprintf('4:%s', $section->id);
                        $sectionHead = $heads[$sectionKey] ?? null;
                        $section->head_employee_name = $sectionHead['employee_name'] ?? null;
                        $section->head_job_position = $sectionHead['job_position_name'] ?? null;
                    }
                }
            }
        }

        return $directors;
    }
}
