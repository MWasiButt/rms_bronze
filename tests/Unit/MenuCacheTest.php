<?php

namespace Tests\Unit;

use App\Support\MenuCache;
use Tests\TestCase;

class MenuCacheTest extends TestCase
{
    public function test_menu_cache_key_is_tenant_and_outlet_scoped(): void
    {
        $cache = new MenuCache();

        $this->assertSame('tenant:7:outlet:3:menu', $cache->key(7, 3));
        $this->assertSame('tenant:7:outlet:all:menu', $cache->key(7));
    }
}
