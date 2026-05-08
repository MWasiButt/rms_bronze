<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureFlag
{
    public function handle(Request $request, Closure $next, string $flag): Response
    {
        $settings = $request->user()?->tenant?->settings;

        abort_if(! $settings || ! (bool) data_get($settings, $flag), 404);

        return $next($request);
    }
}
