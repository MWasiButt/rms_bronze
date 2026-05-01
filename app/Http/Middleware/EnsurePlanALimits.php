<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\PlanALimits;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanALimits
{
    public function __construct(private readonly PlanALimits $planLimits)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->session()->get('tenant_id') ?? $request->user()?->tenant_id;

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);

            abort_if(! $tenant || $tenant->status !== 'active', 403, 'This business account is not active.');

            $this->planLimits->assertTenantWithinLimits($tenant);
        }

        return $next($request);
    }
}
