<?php

namespace App\Services\UserSettingsService\DTOs;

readonly class ModulMenuData
{
    public function __construct(
        public string $modulName,
        public ?string $menuName,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $modulName = trim((string) ($data['modul_name'] ?? ''));
        $menuName = array_key_exists('menu_name', $data) ? trim((string) $data['menu_name']) : '';

        return new self(
            modulName: $modulName,
            menuName: $menuName !== '' ? $menuName : null,
        );
    }
}
