<?php

namespace Tests\Unit;

use App\Http\Controllers\SettingsController;
use ReflectionMethod;
use Tests\TestCase;

class BronzeFeatureFlagsTest extends TestCase
{
    public function test_bronze_feature_flags_match_plan_defaults(): void
    {
        $method = new ReflectionMethod(SettingsController::class, 'bronzeFeatureFlags');
        $method->setAccessible(true);

        $this->assertSame([
            'qr_ordering' => false,
            'delivery' => false,
            'inventory_basic' => true,
            'kds_basic' => true,
            'api_read' => false,
        ], $method->invoke(new SettingsController()));
    }
}
