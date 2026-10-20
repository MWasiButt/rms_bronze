<?php

namespace Tests\Unit;

use App\Models\TenantSetting;
use App\Support\PlanALimits;
use Tests\TestCase;

class GoldPlanSetupTest extends TestCase
{
    public function test_gold_limit_defaults_match_plan(): void
    {
        $this->assertSame([
            'max_outlets' => 1,
            'max_pos_devices' => 5,
            'max_active_users' => 10,
        ], PlanALimits::defaults(PlanALimits::PLAN_GOLD));
    }

    public function test_gold_feature_flags_match_plan_defaults(): void
    {
        $this->assertSame([
            'qr_ordering' => true,
            'delivery' => false,
            'inventory_basic' => false,
            'inventory_advanced' => true,
            'kds_basic' => true,
            'kds_pro' => false,
            'coupons' => true,
            'loyalty_basic' => true,
            'api_read' => false,
            'webhooks' => false,
            'multi_outlet' => false,
            'commissary' => false,
            'analytics_advanced' => true,
            'role_permissions_advanced' => true,
            'shift_reports' => true,
        ], PlanALimits::featureFlags(PlanALimits::PLAN_GOLD));
    }

    public function test_tenant_settings_accept_gold_feature_flags(): void
    {
        $setting = new TenantSetting(PlanALimits::featureFlags(PlanALimits::PLAN_GOLD));

        $this->assertTrue($setting->qr_ordering);
        $this->assertFalse($setting->inventory_basic);
        $this->assertTrue($setting->inventory_advanced);
        $this->assertTrue($setting->coupons);
        $this->assertTrue($setting->loyalty_basic);
        $this->assertTrue($setting->analytics_advanced);
        $this->assertTrue($setting->role_permissions_advanced);
        $this->assertTrue($setting->shift_reports);
    }
}
