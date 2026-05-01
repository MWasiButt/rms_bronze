<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Support\Audit;
use App\Support\MenuCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        return view('menu-categories', [
            'categories' => ProductCategory::withCount('menuItems')
                ->where('tenant_id', $tenantId)
                ->withTrashed()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'modifierGroups' => \App\Models\ModifierGroup::with('options')
                ->where('tenant_id', $tenantId)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('catalog.category-form', [
            'category' => new ProductCategory(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(Request $request, MenuCache $menuCache): RedirectResponse
    {
        $validated = $this->validatedCategory($request);
        $category = ProductCategory::create([
            'tenant_id' => $request->user()->tenant_id,
            'outlet_id' => $request->user()->outlet_id,
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($request, $validated['name']),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        Audit::record('menu.category.created', $category, [], $category->getAttributes(), $request);
        $menuCache->forget($request->user()->tenant_id, $request->user()->outlet_id);

        return redirect()->route('menu-categories.index')->with('status', 'Category created.');
    }

    public function edit(Request $request, ProductCategory $menuCategory): View
    {
        $this->authorizeTenantCategory($request, $menuCategory);

        return view('catalog.category-form', ['category' => $menuCategory]);
    }

    public function update(Request $request, ProductCategory $menuCategory, MenuCache $menuCache): RedirectResponse
    {
        $this->authorizeTenantCategory($request, $menuCategory);

        $validated = $this->validatedCategory($request, $menuCategory);
        $oldValues = $menuCategory->getOriginal();

        $menuCategory->update([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($request, $validated['name'], $menuCategory),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        Audit::record('menu.category.updated', $menuCategory, $oldValues, $menuCategory->getChanges(), $request);
        $menuCache->forget($request->user()->tenant_id, $request->user()->outlet_id);

        return redirect()->route('menu-categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Request $request, ProductCategory $menuCategory, MenuCache $menuCache): RedirectResponse
    {
        $this->authorizeTenantCategory($request, $menuCategory);
        $oldValues = $menuCategory->getOriginal();
        $menuCategory->delete();
        Audit::record('menu.category.archived', $menuCategory, $oldValues, ['deleted_at' => $menuCategory->deleted_at], $request);
        $menuCache->forget($request->user()->tenant_id, $request->user()->outlet_id);

        return back()->with('status', 'Category archived.');
    }

    public function restore(Request $request, int $category, MenuCache $menuCache): RedirectResponse
    {
        $model = ProductCategory::withTrashed()
            ->where('tenant_id', $request->user()->tenant_id)
            ->findOrFail($category);

        $model->restore();
        Audit::record('menu.category.restored', $model, ['deleted_at' => $model->getOriginal('deleted_at')], ['deleted_at' => null], $request);
        $menuCache->forget($request->user()->tenant_id, $request->user()->outlet_id);

        return back()->with('status', 'Category restored.');
    }

    /**
     * @return array{name: string, sort_order?: int|null}
     */
    private function validatedCategory(Request $request, ?ProductCategory $category = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_categories', 'name')
                    ->where('tenant_id', $request->user()->tenant_id)
                    ->ignore($category?->id),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function authorizeTenantCategory(Request $request, ProductCategory $category): void
    {
        abort_if($category->tenant_id !== $request->user()->tenant_id, 404);
    }

    private function uniqueSlug(Request $request, string $name, ?ProductCategory $category = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (ProductCategory::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('slug', $slug)
            ->when($category, fn ($query) => $query->whereKeyNot($category->id))
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
