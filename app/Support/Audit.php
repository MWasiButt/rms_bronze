<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Audit
{
    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public static function record(
        string $event,
        ?Model $auditable = null,
        array $oldValues = [],
        array $newValues = [],
        ?Request $request = null,
        ?int $tenantId = null,
        ?int $userId = null
    ): AuditLog {
        $request ??= request();
        $user = $request?->user() ?: Auth::user();

        return AuditLog::create([
            'tenant_id' => $tenantId ?? $auditable?->getAttribute('tenant_id') ?? $user?->tenant_id,
            'user_id' => $userId ?? $user?->id,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'event' => $event,
            'old_values' => self::clean($oldValues),
            'new_values' => self::clean($newValues),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private static function clean(array $values): array
    {
        unset($values['password'], $values['remember_token']);

        return $values;
    }
}
