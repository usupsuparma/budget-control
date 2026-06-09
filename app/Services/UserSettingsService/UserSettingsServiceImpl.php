<?php

namespace App\Services\UserSettingsService;

use App\Exceptions\ModulMenuAlreadyExistsException;
use App\Exceptions\ModulMenuInUseException;
use App\Exceptions\ModulMenuNotFoundException;
use App\Helpers\PermissionHelper;
use App\Models\Employee;
use App\Models\ModulMenu;
use App\Models\Permission;
use App\Services\UserSettingsService\DTOs\ModulMenuData;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserSettingsServiceImpl implements UserSettingsService
{
    public function getPageData(): array
    {
        return [
            'roles' => Role::query()->orderBy('name')->get(),
            'employees' => Employee::query()
                ->select('id', 'first_name', 'last_name', 'email')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(),
            'permissions' => Permission::query()
                ->with('modul')
                ->orderByDesc('id')
                ->get(),
            'moduls' => ModulMenu::query()
                ->withCount('permissions')
                ->orderBy('modul_name')
                ->orderBy('menu_name')
                ->get(),
            'routePermissionKeys' => PermissionHelper::routePermissionKeys(),
        ];
    }

    public function createModulMenu(ModulMenuData $data): ModulMenu
    {
        return DB::transaction(function () use ($data) {
            $this->assertUniqueModulMenu($data);

            return ModulMenu::create([
                'modul_name' => $data->modulName,
                'menu_name' => $data->menuName,
            ]);
        });
    }

    public function updateModulMenu(int $id, ModulMenuData $data): ModulMenu
    {
        return DB::transaction(function () use ($id, $data) {
            $modulMenu = ModulMenu::query()->find($id);

            if (! $modulMenu) {
                throw new ModulMenuNotFoundException('Data modul tidak ditemukan.');
            }

            $this->assertUniqueModulMenu($data, $modulMenu->id);

            $modulMenu->update([
                'modul_name' => $data->modulName,
                'menu_name' => $data->menuName,
            ]);

            return $modulMenu->fresh(['permissions']);
        });
    }

    public function deleteModulMenu(int $id): void
    {
        DB::transaction(function () use ($id) {
            $modulMenu = ModulMenu::query()->withCount('permissions')->find($id);

            if (! $modulMenu) {
                throw new ModulMenuNotFoundException('Data modul tidak ditemukan.');
            }

            if ($modulMenu->permissions_count > 0) {
                throw new ModulMenuInUseException('Modul tidak dapat dihapus karena masih dipakai oleh permission.');
            }

            $modulMenu->delete();
        });
    }

    private function assertUniqueModulMenu(ModulMenuData $data, ?int $ignoreId = null): void
    {
        $query = ModulMenu::query()
            ->where('modul_name', $data->modulName);

        if ($data->menuName === null) {
            $query->whereNull('menu_name');
        } else {
            $query->where('menu_name', $data->menuName);
        }

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        if ($query->exists()) {
            throw new ModulMenuAlreadyExistsException('Kombinasi modul dan menu sudah ada.');
        }
    }
}
