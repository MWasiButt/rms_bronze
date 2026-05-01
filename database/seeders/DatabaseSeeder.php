<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Outlet;
use App\Models\OutletSetting;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use App\Support\PlanALimits;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed only the minimum data required to log in and start manual testing.
     */
    public function run(): void
    {
        $tenant = Tenant::updateOrCreate(
            ['slug' => 'rms-bronze-demo'],
            [
                'name' => 'RMS Bronze Demo Store',
                'plan_code' => 'bronze',
                'status' => 'active',
                ...PlanALimits::defaults(),
            ]
        );

        $outlet = Outlet::updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Main Outlet',
            ],
            [
                'code' => 'MAIN',
                'phone' => '+92 300 0000000',
                'email' => 'mwasi5276@gmail.com',
                'address' => 'Demo Market, Main Road',
                'is_active' => true,
            ]
        );

        TenantSetting::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'currency' => 'PKR',
                'timezone' => 'Asia/Karachi',
                'receipt_header' => $tenant->name,
                'receipt_footer' => 'Thank you for dining with us.',
                'default_tax_rate_bps' => 1600,
                'qr_ordering' => false,
                'delivery' => false,
                'inventory_basic' => true,
                'kds_basic' => true,
                'api_read' => false,
            ]
        );

        OutletSetting::updateOrCreate(
            ['outlet_id' => $outlet->id],
            [
                'service_charge_enabled' => false,
                'service_charge_bps' => 0,
                'notes' => 'Default outlet for manual testing.',
            ]
        );

        User::updateOrCreate(
            ['email' => 'mwasi5276@gmail.com'],
            [
                'tenant_id' => $tenant->id,
                'outlet_id' => $outlet->id,
                'name' => 'Test Owner',
                'password' => Hash::make('password'),
                'role' => UserRole::OWNER,
                'is_active' => true,
                'last_active_at' => null,
            ]
        );
    }
}
