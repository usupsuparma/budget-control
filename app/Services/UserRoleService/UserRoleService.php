<?php

namespace App\Services\UserRoleService;

interface UserRoleService
{
    /**
     * Check if the given user has administrator access (sees all data).
     *
     * Admin roles are defined in UserRoleServiceImpl::ADMIN_ROLES.
     * Update that constant when role names change in the database.
     */
    public function isAdmin(mixed $user): bool;

    /**
     * Return the Division IDs the user is allowed to see.
     *
     * Admins: returns empty array (caller should skip the filter → show all).
     * Non-admins: resolves via Employment::getDivisionIds().
     */
    public function getDivisionIds(mixed $user): array;

    /**
     * Return the user's primary role name (first role), or null if none.
     */
    public function getPrimaryRole(mixed $user): ?string;
}
