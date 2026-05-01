<?php

namespace App\Actions\Auth;

use App\Enums\UserRole;
use App\Models\Outlet;
use App\Models\OutletSetting;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use App\Support\PlanALimits;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterTenantOwner
{
    /**
     * @param array<string, mixed> $data
     */
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $tenant = Tenant::create([
                'name' => $data['business_name'],
                'slug' => $this->generateUniqueSlug($data['business_name']),
                'plan_code' => 'bronze',
                'status' => 'active',
                ...PlanALimits::defaults(),
            ]);

            $outlet = Outlet::create([
                'tenant_id' => $tenant->id,
                'name' => $data['outlet_name'],
                'code' => Str::upper(Str::substr(Str::slug($data['outlet_name'], ''), 0, 6)) ?: 'MAIN',
                'phone' => Arr::get($data, 'phone'),
                'email' => $data['email'],
                'address' => Arr::get($data, 'address'),
                'is_active' => true,
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

            OutletSetting::create([
                'outlet_id' => $outlet->id,
                'service_charge_enabled' => false,
                'service_charge_bps' => 0,
            ]);

            return User::create([
                'tenant_id' => $tenant->id,
                'outlet_id' => $outlet->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => UserRole::OWNER,
                'is_active' => true,
                'last_active_at' => now(),
            ]);
        });
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
