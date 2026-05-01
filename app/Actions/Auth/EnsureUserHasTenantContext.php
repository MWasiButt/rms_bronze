<?php

namespace App\Actions\Auth;

use App\Models\Outlet;
use App\Models\OutletSetting;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use App\Support\PlanALimits;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnsureUserHasTenantContext
{
    public function handle(User $user): User
    {
        if ($user->tenant_id && $user->outlet_id) {
            return $user;
        }

        return DB::transaction(function () use ($user): User {
            $user->refresh();

            if ($user->tenant_id && $user->outlet_id) {
                return $user;
            }

            $tenant = $user->tenant_id ? Tenant::find($user->tenant_id) : null;

            if (! $tenant) {
                $businessName = $this->defaultBusinessName($user);

                $tenant = Tenant::create([
                    'name' => $businessName,
                    'slug' => $this->generateUniqueSlug($businessName),
                    'plan_code' => 'bronze',
                    'status' => 'active',
                    ...PlanALimits::defaults(),
                ]);

                TenantSetting::create([
                    'tenant_id' => $tenant->id,
                    'currency' => 'USD',
                    'timezone' => config('app.timezone', 'UTC'),
                    'receipt_header' => $tenant->name,
                    'receipt_footer' => 'Thank you for your visit.',
                    'default_tax_rate_bps' => 0,
                    'qr_ordering' => false,
                    'delivery' => false,
                    'inventory_basic' => true,
                    'kds_basic' => true,
                    'api_read' => false,
                ]);
            }

            $outlet = $user->outlet_id ? Outlet::find($user->outlet_id) : null;

            if (! $outlet) {
                $outlet = Outlet::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'name' => 'Main Outlet',
                    ],
                    [
                        'code' => 'MAIN',
                        'email' => $user->email,
                        'is_active' => true,
                    ]
                );

                OutletSetting::firstOrCreate(
                    ['outlet_id' => $outlet->id],
                    [
                        'service_charge_enabled' => false,
                        'service_charge_bps' => 0,
                    ]
                );
            }

            $user->forceFill([
                'tenant_id' => $tenant->id,
                'outlet_id' => $outlet->id,
            ])->save();

            return $user->fresh();
        });
    }

    private function defaultBusinessName(User $user): string
    {
        return Str::of($user->name)
            ->trim()
            ->whenEmpty(fn () => Str::before($user->email, '@'))
            ->title()
            ->append(' Business')
            ->value();
    }

    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
