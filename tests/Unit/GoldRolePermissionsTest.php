<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Http\Middleware\EnsureAbility;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class GoldRolePermissionsTest extends TestCase
{
    public function test_gold_ability_matrix_contains_required_abilities(): void
    {
        $known = config('abilities.known');

        $this->assertContains('orders.manage', $known);
        $this->assertContains('inventory.adjust', $known);
        $this->assertContains('reports.view_cost', $known);
        $this->assertContains('coupons.manage', $known);
        $this->assertContains('loyalty.manage', $known);
    }

    public function test_owner_has_all_abilities_and_cashier_has_order_permissions(): void
    {
        $owner = new User(['role' => UserRole::OWNER]);
        $cashier = new User(['role' => UserRole::CASHIER]);

        $this->assertTrue($owner->hasAbility('reports.view_cost'));
        $this->assertTrue($owner->hasAbility('coupons.manage'));
        $this->assertTrue($cashier->hasAbility('orders.manage'));
        $this->assertFalse($cashier->hasAbility('inventory.adjust'));
    }

    public function test_ability_middleware_blocks_missing_ability_when_gold_permissions_are_enabled(): void
    {
        $request = Request::create('/stock-items/1/movements', 'POST');
        $request->setUserResolver(fn () => $this->userWithRoleAndAdvancedPermissions(UserRole::CASHIER, true));

        $this->expectException(HttpException::class);

        app(EnsureAbility::class)->handle($request, fn () => response('ok'), 'inventory.adjust');
    }

    public function test_ability_middleware_allows_legacy_bronze_when_advanced_permissions_are_disabled(): void
    {
        $request = Request::create('/stock-items/1/movements', 'POST');
        $request->setUserResolver(fn () => $this->userWithRoleAndAdvancedPermissions(UserRole::CASHIER, false));

        $response = app(EnsureAbility::class)->handle($request, fn () => response('ok'), 'inventory.adjust');

        $this->assertSame('ok', $response->getContent());
    }

    public function test_gold_routes_have_ability_guards(): void
    {
        $this->assertContains('ability:orders.manage', $this->middlewareFor('orders.start'));
        $this->assertContains('ability:tables.manage', $this->middlewareFor('tables.store'));
        $this->assertContains('ability:inventory.adjust', $this->middlewareFor('stock-items.movements.store'));
        $this->assertContains('ability:reports.sales.view', $this->middlewareFor('reports.sales'));
        $this->assertContains('ability:settings.manage', $this->middlewareFor('settings.update'));
    }

    private function userWithRoleAndAdvancedPermissions(UserRole $role, bool $enabled): User
    {
        $tenant = new Tenant([
            'name' => 'Gold Tenant',
            'slug' => 'gold-tenant',
            'plan_code' => 'gold',
            'status' => 'active',
        ]);

        $tenant->setRelation('settings', new TenantSetting([
            'role_permissions_advanced' => $enabled,
        ]));

        $user = new User([
            'tenant_id' => 1,
            'outlet_id' => 1,
            'role' => $role,
        ]);
        $user->setRelation('tenant', $tenant);

        return $user;
    }

    /**
     * @return array<int, string>
     */
    private function middlewareFor(string $routeName): array
    {
        $route = Route::getRoutes()->getByName($routeName);

        $this->assertNotNull($route, $routeName.' route does not exist.');

        return $route->gatherMiddleware();
    }
}
