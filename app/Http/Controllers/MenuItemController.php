<?php

namespace App\Http\Controllers;

use App\Enums\TaxMode;
use App\Models\MenuItem;
use App\Models\ModifierGroup;
use App\Models\ProductCategory;
use App\Support\Audit;
use App\Support\MenuCache;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(Request $request): View
    {
        return view('menu-items', [
            'items' => MenuItem::with(['category', 'modifierGroups'])
                ->where('tenant_id', $request->user()->tenant_id)
                ->withTrashed()
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return $this->form($request, new MenuItem([
            'tax_mode' => TaxMode::EXCLUSIVE,
            'tax_rate_bps' => $request->user()->tenant?->settings?->default_tax_rate_bps ?? 0,
            'is_active' => true,
        ]));
    }

    public function store(Request $request, MenuCache $menuCache): RedirectResponse
    {
        $validated = $this->validatedItem($request);

        $item = MenuItem::create([
            'tenant_id' => $request->user()->tenant_id,
            'outlet_id' => $request->user()->outlet_id,
            'product_category_id' => $validated['product_category_id'],
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?: null,
            'description' => $validated['description'] ?? null,
            'price_cents' => Money::toCents($validated['price']),
            'cost_cents' => Money::toCents($validated['cost'] ?? 0),
            'tax_rate_bps' => (int) round(((float) $validated['tax_rate']) * 100),
            'tax_mode' => TaxMode::from($validated['tax_mode']),
            'is_active' => $request->boolean('is_active'),
        ]);

        $item->modifierGroups()->sync($validated['modifier_groups'] ?? []);
        Audit::record('menu.item.created', $item, [], [
            ...$item->getAttributes(),
            'modifier_group_ids' => $validated['modifier_groups'] ?? [],
        ], $request);
        $menuCache->forget($request->user()->tenant_id, $request->user()->outlet_id);

        return redirect()->route('menu-items.index')->with('status', 'Menu item created.');
    }

    public function edit(Request $request, MenuItem $menuItem): View
    {
        $this->authorizeTenantItem($request, $menuItem);

        return $this->form($request, $menuItem->load('modifierGroups'));
    }

    public function update(Request $request, MenuItem $menuItem, MenuCache $menuCache): RedirectResponse
    {
        $this->authorizeTenantItem($request, $menuItem);

        $validated = $this->validatedItem($request, $menuItem);
        $oldValues = [
            ...$menuItem->getOriginal(),
            'modifier_group_ids' => $menuItem->modifierGroups()->pluck('modifier_groups.id')->all(),
        ];

        $menuItem->update([
            'product_category_id' => $validated['product_category_id'],
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?: null,
            'description' => $validated['description'] ?? null,
            'price_cents' => Money::toCents($validated['price']),
            'cost_cents' => Money::toCents($validated['cost'] ?? 0),
            'tax_rate_bps' => (int) round(((float) $validated['tax_rate']) * 100),
            'tax_mode' => TaxMode::from($validated['tax_mode']),
            'is_active' => $request->boolean('is_active'),
        ]);

        $menuItem->modifierGroups()->sync($validated['modifier_groups'] ?? []);
        Audit::record('menu.item.updated', $menuItem, $oldValues, [
            ...$menuItem->fresh()->getAttributes(),
            'modifier_group_ids' => $validated['modifier_groups'] ?? [],
        ], $request);
        $menuCache->forget($request->user()->tenant_id, $request->user()->outlet_id);

        return redirect()->route('menu-items.index')->with('status', 'Menu item updated.');
    }

    public function destroy(Request $request, MenuItem $menuItem, MenuCache $menuCache): RedirectResponse
    {
        $this->authorizeTenantItem($request, $menuItem);
        $oldValues = $menuItem->getOriginal();
        $menuItem->delete();
        Audit::record('menu.item.archived', $menuItem, $oldValues, ['deleted_at' => $menuItem->deleted_at], $request);
        $menuCache->forget($request->user()->tenant_id, $request->user()->outlet_id);

        return back()->with('status', 'Menu item archived.');
    }

    public function restore(Request $request, int $item, MenuCache $menuCache): RedirectResponse
    {
        $model = MenuItem::withTrashed()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($item);

        $model->restore();
        Audit::record('menu.item.restored', $model, ['deleted_at' => $model->getOriginal('deleted_at')], ['deleted_at' => null], $request);
        $menuCache->forget($request->user()->tenant_id, $request->user()->outlet_id);

        return back()->with('status', 'Menu item restored.');
    }

    private function form(Request $request, MenuItem $item): View
    {
        return view('menu-item-editor', [
            'item' => $item,
            'categories' => ProductCategory::where('tenant_id', $request->user()->tenant_id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'modifierGroups' => ModifierGroup::with('options')
                ->where('tenant_id', $request->user()->tenant_id)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'taxModes' => TaxMode::cases(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedItem(Request $request, ?MenuItem $item = null): array
    {
        return $request->validate([
            'product_category_id' => [
                'required',
                Rule::exists('product_categories', 'id')->where('tenant_id', $request->user()->tenant_id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('menu_items', 'sku')
                    ->where('tenant_id', $request->user()->tenant_id)
                    ->ignore($item?->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'tax_mode' => ['required', Rule::enum(TaxMode::class)],
            'modifier_groups' => ['array'],
            'modifier_groups.*' => [
                'integer',
                Rule::exists('modifier_groups', 'id')->where('tenant_id', $request->user()->tenant_id),
            ],
        ]);
    }

    private function authorizeTenantItem(Request $request, MenuItem $item): void
    {
        abort_if($item->tenant_id !== $request->user()->tenant_id, 404);
    }

}
