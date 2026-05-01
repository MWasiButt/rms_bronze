<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\Outlet;
use App\Models\Tenant;
use App\Models\User;
use App\Support\PlanALimits;
use App\Support\TenantContext;
use Laravel\Sanctum\HasApiTokens;
use Tests\TestCase;

class BronzeFoundationTest extends TestCase
{
    public function test_bronze_limit_defaults_match_plan(): void
    {
        $this->assertSame([
            'max_outlets' => 1,
            'max_pos_devices' => 2,
            'max_active_users' => 3,
        ], PlanALimits::defaults());
    }

    public function test_bronze_roles_match_plan(): void
    {
        $this->assertSame([
            'OWNER',
            'CASHIER',
            'KITCHEN',
        ], array_map(fn (UserRole $role) => $role->value, UserRole::cases()));
    }

    public function test_tenant_context_matches_current_tenant_and_outlet_only(): void
    {
        $tenant = new Tenant(['name' => 'Demo Tenant']);
        $tenant->id = 10;

        $outlet = new Outlet(['tenant_id' => 10, 'name' => 'Main Outlet']);
        $outlet->id = 20;

        $context = new TenantContext($tenant, $outlet, new User());

        $this->assertTrue($context->belongsToTenant(10));
        $this->assertFalse($context->belongsToTenant(11));
        $this->assertTrue($context->belongsToOutlet(20));
        $this->assertFalse($context->belongsToOutlet(21));
    }

    public function test_user_model_uses_sanctum_api_tokens(): void
    {
        $this->assertContains(HasApiTokens::class, class_uses_recursive(User::class));
    }
}
