<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAbility
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $user = $request->user();
        $settings = $user?->tenant?->settings;

        if (! (bool) $settings?->role_permissions_advanced) {
            return $next($request);
        }

        abort_if(! $user?->hasAbility($ability), 403, 'You do not have the required ability: '.$ability.'.');

        return $next($request);
    }
}
