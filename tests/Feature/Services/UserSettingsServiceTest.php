<?php

namespace Tests\Feature\Services;

use App\Exceptions\ModulMenuAlreadyExistsException;
use App\Exceptions\ModulMenuInUseException;
use App\Models\Employee;
use App\Models\ModulMenu;
use App\Models\Permission;
use App\Services\UserSettingsService\DTOs\ModulMenuData;
use App\Services\UserSettingsService\UserSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserSettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserSettingsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(UserSettingsService::class);
    }

    public function test_get_page_data_returns_expected_collections(): void
    {
        $modul = ModulMenu::create([
            'modul_name' => 'Test Settings',
            'menu_name' => 'Test Users',
        ]);

        Permission::create([
            'name' => 'test.settings.users.view',
            'guard_name' => 'web',
            'modul_menu' => $modul->id,
            'modul_menu_name' => 'Users View',
        ]);

        Role::create([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        Employee::create([
            'first_name' => 'Alice',
            'last_name' => 'Admin',
            'email' => 'alice@example.com',
        ]);

        $data = $this->service->getPageData();

        $this->assertTrue($data['moduls']->contains(fn (ModulMenu $row) => $row->id === $modul->id));
        $this->assertTrue($data['permissions']->contains(fn (Permission $row) => $row->name === 'test.settings.users.view'));
        $this->assertTrue($data['roles']->contains(fn (Role $row) => $row->name === 'Admin'));
        $this->assertTrue($data['employees']->contains(fn (Employee $row) => $row->email === 'alice@example.com'));
        $this->assertIsArray($data['routePermissionKeys']);
        $this->assertEquals(1, $data['moduls']->firstWhere('id', $modul->id)?->permissions_count);
    }

    public function test_create_modul_menu_creates_record(): void
    {
        $result = $this->service->createModulMenu(new ModulMenuData(
            modulName: 'Quality Assurance',
            menuName: 'Automation',
        ));

        $this->assertDatabaseHas('modul_menu', [
            'id' => $result->id,
            'modul_name' => 'Quality Assurance',
            'menu_name' => 'Automation',
        ]);
    }

    public function test_create_modul_menu_rejects_duplicate_combination(): void
    {
        ModulMenu::create([
            'modul_name' => 'Settings',
            'menu_name' => 'Users',
        ]);

        $this->expectException(ModulMenuAlreadyExistsException::class);

        $this->service->createModulMenu(new ModulMenuData(
            modulName: 'Settings',
            menuName: 'Users',
        ));
    }

    public function test_update_modul_menu_updates_record(): void
    {
        $modul = ModulMenu::create([
            'modul_name' => 'Settings',
            'menu_name' => 'Users',
        ]);

        $updated = $this->service->updateModulMenu($modul->id, new ModulMenuData(
            modulName: 'Settings',
            menuName: 'Permissions',
        ));

        $this->assertEquals('Permissions', $updated->menu_name);
        $this->assertDatabaseHas('modul_menu', [
            'id' => $modul->id,
            'modul_name' => 'Settings',
            'menu_name' => 'Permissions',
        ]);
    }

    public function test_delete_modul_menu_soft_deletes_unused_record(): void
    {
        $modul = ModulMenu::create([
            'modul_name' => 'Settings',
            'menu_name' => 'Users',
        ]);

        $this->service->deleteModulMenu($modul->id);

        $this->assertSoftDeleted('modul_menu', [
            'id' => $modul->id,
        ]);
    }

    public function test_delete_modul_menu_rejects_when_used_by_permission(): void
    {
        $modul = ModulMenu::create([
            'modul_name' => 'Settings',
            'menu_name' => 'Users',
        ]);

        Permission::create([
            'name' => 'setting.users.view',
            'guard_name' => 'web',
            'modul_menu' => $modul->id,
            'modul_menu_name' => 'Users View',
        ]);

        $this->expectException(ModulMenuInUseException::class);

        $this->service->deleteModulMenu($modul->id);
    }
}
