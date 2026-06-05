<?php

namespace Tests\Unit;

use App\Helpers\PermissionHelper;
use Tests\TestCase;

class PermissionHelperTest extends TestCase
{
    public function test_route_permission_keys_are_loaded_from_active_web_route_middleware(): void
    {
        $keys = PermissionHelper::routePermissionKeys();

        $this->assertContains('setting.users.view', $keys);
        $this->assertContains('authorization.view', $keys);
        $this->assertNotContains('kpi.kpidivision.view', $keys);
        $this->assertSame($keys, array_values(array_unique($keys)));
        $this->assertSame($keys, collect($keys)->sort()->values()->all());
    }
}
