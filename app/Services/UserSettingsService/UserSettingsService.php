<?php

namespace App\Services\UserSettingsService;

use App\Models\ModulMenu;
use App\Services\UserSettingsService\DTOs\ModulMenuData;

interface UserSettingsService
{
    public function getPageData(): array;

    public function createModulMenu(ModulMenuData $data): ModulMenu;

    public function updateModulMenu(int $id, ModulMenuData $data): ModulMenu;

    public function deleteModulMenu(int $id): void;
}
