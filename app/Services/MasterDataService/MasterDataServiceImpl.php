<?php

namespace App\Services\MasterDataService;

use App\Models\Director;
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
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
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
    }
}
