<?php

namespace App\Http\Middleware;

use App\Models\Outlet;
use App\Models\Tenant;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if(! $user || ! $user->tenant_id || ! $user->outlet_id, 403, 'Your account is not linked to a tenant and outlet.');
        abort_if(! $user->is_active, 403, 'Your account is inactive. Please contact the account owner.');

        $sessionTenantId = $request->session()->get('tenant_id');
        $sessionOutletId = $request->session()->get('outlet_id');

        if ($request->hasSession()) {
            if (! $sessionTenantId || ! $sessionOutletId) {
                $request->session()->put([
                    'tenant_id' => $user->tenant_id,
                    'outlet_id' => $user->outlet_id,
                    'user_role' => $user->role?->value,
                ]);
            } else {
                abort_if((int) $sessionTenantId !== (int) $user->tenant_id, 403, 'Invalid tenant context.');
                abort_if((int) $sessionOutletId !== (int) $user->outlet_id, 403, 'Invalid outlet context.');
            }
        }

        $tenant = Tenant::find($user->tenant_id);
        $outlet = Outlet::where('tenant_id', $user->tenant_id)->find($user->outlet_id);

        abort_if(! $tenant || $tenant->status !== 'active', 403, 'This tenant is not active.');
        abort_if(! $outlet || ! $outlet->is_active, 403, 'This outlet is not active.');

        $context = new TenantContext($tenant, $outlet, $user);

        app()->instance(TenantContext::class, $context);
        $request->attributes->set('tenantContext', $context);

        return $next($request);
    }
}
