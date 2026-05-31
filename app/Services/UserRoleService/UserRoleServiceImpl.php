<?php

namespace App\Services\UserRoleService;

class UserRoleServiceImpl implements UserRoleService
{
    /**
     * Single source of truth for admin role names.
     *
     * When a role is renamed in the database, only this constant needs to change.
     */
    public const ADMIN_ROLES = [
        'Super Admin',
    ];

    /**
     * Non-admin roles that have scoped (division-level) data access.
     * Ordered by organisational level (highest first).
     */
    public const SCOPED_ROLES = [
        'Director',
        'Division',
        'Department',
        'Section',
        'User',
    ];

    public function isAdmin(mixed $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(self::ADMIN_ROLES);
    }

    public function getDivisionIds(mixed $user): array
    {
        if ($this->isAdmin($user)) {
            return [];
        }

        $employment = $user?->employment;

        if (! $employment) {
            return [];
        }

        return $employment->getDivisionIds();
    }

    public function getPrimaryRole(mixed $user): ?string
    {
        if (! $user) {
            return null;
        }

        return $user->getRoleNames()->first();
    }
}
