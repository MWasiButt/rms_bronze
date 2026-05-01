<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\EnsurePlanALimits;
use App\Http\Middleware\EnsureTenantContext;
use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/');
        $middleware->alias([
            'plan.a' => EnsurePlanALimits::class,
            'bronze.limits' => EnsurePlanALimits::class,
            'tenant.context' => EnsureTenantContext::class,
            'role' => EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
