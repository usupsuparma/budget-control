<?php

namespace App\Services\MasterDataService;

use Illuminate\Database\Eloquent\Collection;

interface MasterDataService
{
    /**
     * Load full organizational tree: Directors → Divisions → Departments → Sections.
     * Only active records at each level are included, ordered alphabetically by name.
     *
     * @return Collection<\App\Models\Director>
     */
    /**
     * Get all master data options for dropdowns.
     * Includes JobPositions, JobLevels, Directors, Divisions, Departments, and Sections.
     *
     * @return array
     */
    public function getAllOptions(): array;

    /**
     * Invalidate the cached organization tree.
     * Call this after any CRUD operation on Director, Division, Department, or Section.
     */
    public function forgetCache(): void;

    public function getOrganizationTree(): Collection;
}
