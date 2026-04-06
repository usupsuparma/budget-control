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
    public function getOrganizationTree(): Collection;

    /**
     * Invalidate the cached organization tree.
     * Call this after any CRUD operation on Director, Division, Department, or Section.
     */
    public function forgetCache(): void;
}
