<?php

namespace App\Http\Controllers;

use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Support\Audit;
use App\Support\MenuCache;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ModifierOptionController extends Controller
{
    public function create(Request $request, ModifierGroup $modifierGroup): View
    {
        $this->authorizeTenantGroup($request, $modifierGroup);

        return view('catalog.modifier-option-form', [
            'group' => $modifierGroup,
            'option' => new ModifierOption(['price_delta_cents' => 0, 'sort_order' => 0, 'is_active' => true]),
        ]);
    }

    public function store(Request $request, ModifierGroup $modifierGroup, MenuCache $menuCache): RedirectResponse
    {
        $this->authorizeTenantGroup($request, $modifierGroup);
        $validated = $this->validatedOption($request);

        $option = $modifierGroup->options()->create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $validated['name'],
            'price_delta_cents' => Money::toCents($validated['price_delta'] ?? 0),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        Audit::record('menu.modifier_option.created', $option, [], $option->getAttributes(), $request);
        $menuCache->forget($request->user()->tenant_id, $request->user()->outlet_id);

        return redirect()->route('menu-categories.index')->with('status', 'Modifier option created.');
    }

    public function edit(Request $request, ModifierGroup $modifierGroup, ModifierOption $modifierOption): View
    {
        $this->authorizeTenantOption($request, $modifierGroup, $modifierOption);

        return view('catalog.modifier-option-form', [
            'group' => $modifierGroup,
            'option' => $modifierOption,
        ]);
    }

    public function update(Request $request, ModifierGroup $modifierGroup, ModifierOption $modifierOption, MenuCache $menuCache): RedirectResponse
    {
        $this->authorizeTenantOption($request, $modifierGroup, $modifierOption);
        $validated = $this->validatedOption($request, $modifierOption);
        $oldValues = $modifierOption->getOriginal();

        $modifierOption->update([
            'name' => $validated['name'],
            'price_delta_cents' => Money::toCents($validated['price_delta'] ?? 0),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        Audit::record('menu.modifier_option.updated', $modifierOption, $oldValues, $modifierOption->getChanges(), $request);
        $menuCache->forget($request->user()->tenant_id, $request->user()->outlet_id);

        return redirect()->route('menu-categories.index')->with('status', 'Modifier option updated.');
    }

    public function destroy(Request $request, ModifierGroup $modifierGroup, ModifierOption $modifierOption, MenuCache $menuCache): RedirectResponse
    {
        $this->authorizeTenantOption($request, $modifierGroup, $modifierOption);
        $oldValues = $modifierOption->getOriginal();
        $modifierOption->delete();
        Audit::record('menu.modifier_option.deleted', $modifierOption, $oldValues, ['deleted' => true], $request);
        $menuCache->forget($request->user()->tenant_id, $request->user()->outlet_id);

        return back()->with('status', 'Modifier option deleted.');
    }

    /**
     * @return array{name: string, price_delta?: float|int|string|null, sort_order?: int|null}
     */
    private function validatedOption(Request $request, ?ModifierOption $option = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('modifier_options', 'name')
                    ->where('tenant_id', $request->user()->tenant_id)
                    ->where('modifier_group_id', $request->route('modifierGroup')->id)
                    ->ignore($option?->id),
            ],
            'price_delta' => ['nullable', 'numeric', 'min:-999999', 'max:999999'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function authorizeTenantGroup(Request $request, ModifierGroup $group): void
    {
        abort_if($group->tenant_id !== $request->user()->tenant_id, 404);
    }

    private function authorizeTenantOption(Request $request, ModifierGroup $group, ModifierOption $option): void
    {
        $this->authorizeTenantGroup($request, $group);
        abort_if($option->tenant_id !== $request->user()->tenant_id || $option->modifier_group_id !== $group->id, 404);
    }

}
