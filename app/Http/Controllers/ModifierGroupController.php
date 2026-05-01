<?php

namespace App\Http\Controllers;

use App\Models\ModifierGroup;
use App\Support\Audit;
use App\Support\MenuCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ModifierGroupController extends Controller
{
    public function create(): View
    {
        return view('catalog.modifier-group-form', [
            'group' => new ModifierGroup(['min_select' => 0, 'max_select' => 1, 'is_required' => false, 'sort_order' => 0]),
        ]);
    }

    public function store(Request $request, MenuCache $menuCache): RedirectResponse
    {
        $validated = $this->validatedGroup($request);

        $group = ModifierGroup::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $validated['name'],
            'min_select' => $validated['min_select'] ?? 0,
            'max_select' => $validated['max_select'] ?? 1,
            'is_required' => $request->boolean('is_required'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        Audit::record('menu.modifier_group.created', $group, [], $group->getAttributes(), $request);
        $menuCache->forget($request->user()->tenant_id, $request->user()->outlet_id);

        return redirect()->route('menu-categories.index')->with('status', 'Modifier group created.');
    }

    public function edit(Request $request, ModifierGroup $modifierGroup): View
    {
        $this->authorizeTenantGroup($request, $modifierGroup);

        return view('catalog.modifier-group-form', ['group' => $modifierGroup]);
    }

    public function update(Request $request, ModifierGroup $modifierGroup, MenuCache $menuCache): RedirectResponse
    {
        $this->authorizeTenantGroup($request, $modifierGroup);
        $validated = $this->validatedGroup($request, $modifierGroup);
        $oldValues = $modifierGroup->getOriginal();

        $modifierGroup->update([
            'name' => $validated['name'],
            'min_select' => $validated['min_select'] ?? 0,
            'max_select' => $validated['max_select'] ?? 1,
            'is_required' => $request->boolean('is_required'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        Audit::record('menu.modifier_group.updated', $modifierGroup, $oldValues, $modifierGroup->getChanges(), $request);
        $menuCache->forget($request->user()->tenant_id, $request->user()->outlet_id);

        return redirect()->route('menu-categories.index')->with('status', 'Modifier group updated.');
    }

    public function destroy(Request $request, ModifierGroup $modifierGroup, MenuCache $menuCache): RedirectResponse
    {
        $this->authorizeTenantGroup($request, $modifierGroup);
        $oldValues = $modifierGroup->getOriginal();
        $modifierGroup->delete();
        Audit::record('menu.modifier_group.deleted', $modifierGroup, $oldValues, ['deleted' => true], $request);
        $menuCache->forget($request->user()->tenant_id, $request->user()->outlet_id);

        return back()->with('status', 'Modifier group deleted.');
    }

    /**
     * @return array{name: string, min_select?: int|null, max_select?: int|null, sort_order?: int|null}
     */
    private function validatedGroup(Request $request, ?ModifierGroup $group = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('modifier_groups', 'name')
                    ->where('tenant_id', $request->user()->tenant_id)
                    ->ignore($group?->id),
            ],
            'min_select' => ['nullable', 'integer', 'min:0'],
            'max_select' => ['nullable', 'integer', 'min:1', 'gte:min_select'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function authorizeTenantGroup(Request $request, ModifierGroup $group): void
    {
        abort_if($group->tenant_id !== $request->user()->tenant_id, 404);
    }
}
