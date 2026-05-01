<?php

namespace App\Http\Controllers;

use App\Models\OutletSetting;
use App\Models\TenantSetting;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $tenant = $request->user()->tenant;
        $outlet = $request->user()->outlet;
        $oldValues = [
            'tenant' => $tenant->only(['id', 'name']),
            'tenant_settings' => $tenant->settings?->getAttributes() ?? [],
            'outlet' => $outlet->only(['id', 'name', 'code', 'phone', 'email', 'address']),
            'outlet_settings' => $outlet->settings?->getAttributes() ?? [],
        ];

        return view('settings.edit', [
            'tenant' => $tenant,
            'outlet' => $outlet,
            'tenantSetting' => $tenant->settings ?: $this->defaultTenantSettings($tenant->id),
            'outletSetting' => $outlet->settings ?: new OutletSetting([
                'outlet_id' => $outlet->id,
                'service_charge_enabled' => false,
                'service_charge_bps' => 0,
            ]),
            'timezones' => timezone_identifiers_list(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        $outlet = $request->user()->outlet;

        $validated = $request->validate([
            'currency' => ['required', 'string', 'size:3'],
            'timezone' => ['required', 'timezone'],
            'receipt_header' => ['nullable', 'string', 'max:1000'],
            'receipt_footer' => ['nullable', 'string', 'max:1000'],
            'default_tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'outlet_name' => ['required', 'string', 'max:255'],
            'outlet_code' => ['nullable', 'string', 'max:255'],
            'outlet_phone' => ['nullable', 'string', 'max:255'],
            'outlet_email' => ['nullable', 'email', 'max:255'],
            'outlet_address' => ['nullable', 'string', 'max:1000'],
            'service_charge_enabled' => ['nullable', 'boolean'],
            'service_charge_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'service_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $tenantSetting = TenantSetting::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'currency' => strtoupper($validated['currency']),
                'timezone' => $validated['timezone'],
                'receipt_header' => $validated['receipt_header'] ?? null,
                'receipt_footer' => $validated['receipt_footer'] ?? null,
                'default_tax_rate_bps' => (int) round((float) $validated['default_tax_rate'] * 100),
                ...$this->bronzeFeatureFlags(),
            ]
        );

        $outlet->update([
            'name' => $validated['outlet_name'],
            'code' => $validated['outlet_code'] ?? null,
            'phone' => $validated['outlet_phone'] ?? null,
            'email' => $validated['outlet_email'] ?? null,
            'address' => $validated['outlet_address'] ?? null,
        ]);

        $outletSetting = OutletSetting::updateOrCreate(
            ['outlet_id' => $outlet->id],
            [
                'service_charge_enabled' => $request->boolean('service_charge_enabled'),
                'service_charge_bps' => (int) round((float) $validated['service_charge_rate'] * 100),
                'notes' => $validated['service_notes'] ?? null,
            ]
        );

        Audit::record('settings.updated', $tenant, $oldValues, [
            'tenant_settings' => $tenantSetting->getAttributes(),
            'outlet' => $outlet->fresh()->only(['id', 'name', 'code', 'phone', 'email', 'address']),
            'outlet_settings' => $outletSetting->getAttributes(),
        ], $request);

        return redirect()->route('settings.edit')->with('status', 'Settings updated.');
    }

    private function defaultTenantSettings(int $tenantId): TenantSetting
    {
        return new TenantSetting([
            'tenant_id' => $tenantId,
            'currency' => 'USD',
            'timezone' => config('app.timezone', 'UTC'),
            'default_tax_rate_bps' => 0,
            ...$this->bronzeFeatureFlags(),
        ]);
    }

    private function bronzeFeatureFlags(): array
    {
        return [
            'qr_ordering' => false,
            'delivery' => false,
            'inventory_basic' => true,
            'kds_basic' => true,
            'api_read' => false,
        ];
    }
}
