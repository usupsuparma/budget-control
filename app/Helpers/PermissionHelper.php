<?php

namespace App\Helpers;

use App\Models\Permission;

class PermissionHelper
{
    public static function routePermissionKeys(): array
    {
        static $keys = null;

        if ($keys !== null) {
            return $keys;
        }

        $routeFile = function_exists('base_path')
            ? base_path('routes/web.php')
            : dirname(__DIR__, 2) . '/routes/web.php';

        if (! is_readable($routeFile)) {
            return $keys = [];
        }

        $contents = file_get_contents($routeFile);

        if ($contents === false) {
            return $keys = [];
        }

        $contents = preg_replace('#/\*.*?\*/#s', '', $contents) ?? $contents;
        $activeLines = [];

        foreach (preg_split('/\R/', $contents) ?: [] as $line) {
            $line = preg_replace('/\/\/.*$/', '', $line) ?? $line;

            if (str_contains($line, 'permission:')) {
                $activeLines[] = $line;
            }
        }

        preg_match_all('/permission:([^\'"\]\),\s]+)/', implode("\n", $activeLines), $matches);

        return $keys = collect($matches[1] ?? [])
            ->flatMap(fn ($value) => explode('|', $value))
            ->map(fn ($key) => trim($key))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public static function registerMenuPermission($name)
    {
        $permissions = [
            "$name.view",
            "$name.create",
            "$name.edit",
            "$name.delete"
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
    }
}
