<?php

use App\Support\TenantContext;
use App\Http\Controllers\PrintAgentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api', 'tenant.context', 'bronze.limits'])->get('/me', function (Request $request, TenantContext $tenantContext) {
    return [
        'user' => [
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'role' => $request->user()->role?->value,
        ],
        'tenant' => [
            'id' => $tenantContext->tenantId(),
            'name' => $tenantContext->tenant()?->name,
        ],
        'outlet' => [
            'id' => $tenantContext->outletId(),
            'name' => $tenantContext->outlet()?->name,
        ],
    ];
});

Route::middleware(['auth:sanctum', 'throttle:api', 'tenant.context', 'bronze.limits'])->group(function () {
    Route::get('/print-agent/jobs/next', [PrintAgentController::class, 'next']);
    Route::patch('/print-agent/jobs/{printJob}', [PrintAgentController::class, 'update']);
});
