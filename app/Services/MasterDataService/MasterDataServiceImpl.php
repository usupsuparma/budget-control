<?php

namespace App\Services\MasterDataService;

use App\Models\Director;
use Illuminate\Database\Eloquent\Collection;

class MasterDataServiceImpl implements MasterDataService
{
    /**
     * Eager-load full organizational tree with only active records at each level.
     *
     * Hierarchy: Director → Division → Department → Section
     * Each level is filtered by status = 'Active' and ordered alphabetically by name.
     *
     * @return Collection<Director>
     */
    public function getOrganizationTree(): Collection
    {
        return Director::where('status', 'Active')
            ->with([
                'divisions' => fn($q) => $q->where('status', 'Active')->orderBy('name'),
                'divisions.departments' => fn($q) => $q->where('status', 'Active')->orderBy('name'),
                'divisions.departments.sections' => fn($q) => $q->where('status', 'Active')->orderBy('name'),
            ])
            ->orderBy('name', 'asc')
            ->get();
    }
}
