<?php

namespace App\Support;

use App\Models\MenuItem;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Cache;

class MenuCache
{
    public function key(int $tenantId, ?int $outletId = null): string
    {
        return 'tenant:'.$tenantId.':outlet:'.($outletId ?: 'all').':menu';
    }

    /**
     * @return array<string, mixed>
     */
    public function remember(int $tenantId, ?int $outletId = null): array
    {
        return Cache::remember($this->key($tenantId, $outletId), now()->addMinutes(15), function () use ($tenantId, $outletId): array {
            $categoryQuery = ProductCategory::query()
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name');

            $itemQuery = MenuItem::query()
                ->with(['category', 'modifierGroups.options'])
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->orderBy('name');

            if ($outletId) {
                $categoryQuery->where(function ($query) use ($outletId) {
                    $query->whereNull('outlet_id')->orWhere('outlet_id', $outletId);
                });

                $itemQuery->where(function ($query) use ($outletId) {
                    $query->whereNull('outlet_id')->orWhere('outlet_id', $outletId);
                });
            }

            return [
                'categories' => $categoryQuery->get(),
                'items' => $itemQuery->get(),
            ];
        });
    }

    public function forget(int $tenantId, ?int $outletId = null): void
    {
        Cache::forget($this->key($tenantId, $outletId));
        Cache::forget($this->key($tenantId));
    }
}
