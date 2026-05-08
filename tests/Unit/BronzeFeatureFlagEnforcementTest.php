<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureFeatureFlag;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class BronzeFeatureFlagEnforcementTest extends TestCase
{
    public function test_disabled_feature_flag_blocks_tenant_request(): void
    {
        $request = Request::create('/api/me', 'GET');
        $request->setUserResolver(fn () => $this->userWithFlags(['api_read' => false]));

        $this->expectException(HttpException::class);

        app(EnsureFeatureFlag::class)->handle($request, fn () => response('ok'), 'api_read');
    }

    public function test_enabled_feature_flag_allows_tenant_request(): void
    {
        $request = Request::create('/api/me', 'GET');
        $request->setUserResolver(fn () => $this->userWithFlags(['api_read' => true]));

        $response = app(EnsureFeatureFlag::class)->handle($request, fn () => response('ok'), 'api_read');

        $this->assertSame('ok', $response->getContent());
    }

    public function test_api_read_route_is_guarded_and_delivery_has_no_bronze_route(): void
    {
        $apiMeRoute = collect(Route::getRoutes())->first(fn ($route) => $route->uri() === 'api/me');

        $this->assertNotNull($apiMeRoute);
        $this->assertContains('feature:api_read', $apiMeRoute->gatherMiddleware());

        $deliveryRoutes = collect(Route::getRoutes())
            ->filter(fn ($route) => str_contains($route->uri(), 'delivery'));

        $this->assertTrue($deliveryRoutes->isEmpty());
    }

    private function userWithFlags(array $flags): User
    {
        $tenant = new Tenant([
            'name' => 'Bronze Tenant',
            'slug' => 'bronze-tenant',
            'plan_code' => 'bronze',
            'status' => 'active',
        ]);

        $tenant->setRelation('settings', new TenantSetting([
            'qr_ordering' => $flags['qr_ordering'] ?? false,
            'delivery' => $flags['delivery'] ?? false,
            'inventory_basic' => $flags['inventory_basic'] ?? true,
            'kds_basic' => $flags['kds_basic'] ?? true,
            'api_read' => $flags['api_read'] ?? false,
        ]));

        $user = new User(['tenant_id' => 1, 'outlet_id' => 1]);
        $user->setRelation('tenant', $tenant);

        return $user;
    }
}
