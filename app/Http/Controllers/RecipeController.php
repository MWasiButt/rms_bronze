<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Recipe;
use App\Models\StockItem;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RecipeController extends Controller
{
    public function index(Request $request): View
    {
        return view('inventory.recipes', [
            'menuItems' => MenuItem::query()
                ->with('recipe.items.stockItem')
                ->where('tenant_id', $request->user()->tenant_id)
                ->where('outlet_id', $request->user()->outlet_id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function edit(Request $request, MenuItem $menuItem): View
    {
        $this->authorizeMenuItem($request, $menuItem);
        $oldValues = $menuItem->recipe?->load('items')->toArray() ?? [];

        return view('inventory.recipe-form', [
            'menuItem' => $menuItem->load('recipe.items.stockItem'),
            'stockItems' => StockItem::query()
                ->where('tenant_id', $request->user()->tenant_id)
                ->where('outlet_id', $request->user()->outlet_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $this->authorizeMenuItem($request, $menuItem);

        $validated = $request->validate([
            'yield_quantity' => ['required', 'numeric', 'min:0.001', 'max:999999'],
            'stock_item_ids' => ['array'],
            'stock_item_ids.*' => ['nullable', 'integer'],
            'quantities' => ['array'],
            'quantities.*' => ['nullable', 'numeric', 'min:0', 'max:999999'],
        ]);

        DB::transaction(function () use ($request, $menuItem, $validated): void {
            $recipe = Recipe::updateOrCreate(
                [
                    'tenant_id' => $request->user()->tenant_id,
                    'menu_item_id' => $menuItem->id,
                ],
                ['yield_quantity' => $validated['yield_quantity']]
            );

            $recipe->items()->delete();

            foreach ($validated['stock_item_ids'] ?? [] as $index => $stockItemId) {
                $quantity = (float) ($validated['quantities'][$index] ?? 0);

                if (! $stockItemId || $quantity <= 0) {
                    continue;
                }

                $stockItem = StockItem::query()
                    ->where('tenant_id', $request->user()->tenant_id)
                    ->where('outlet_id', $request->user()->outlet_id)
                    ->findOrFail($stockItemId);

                $recipe->items()->create([
                    'stock_item_id' => $stockItem->id,
                    'quantity' => $quantity,
                ]);
            }
        });

        Audit::record('stock.recipe.updated', $menuItem, $oldValues, [
            'recipe' => $menuItem->fresh()->recipe?->load('items')->toArray(),
        ], $request);

        return redirect()->route('recipes.index')->with('status', 'Recipe updated.');
    }

    private function authorizeMenuItem(Request $request, MenuItem $menuItem): void
    {
        abort_if(
            $menuItem->tenant_id !== $request->user()->tenant_id || $menuItem->outlet_id !== $request->user()->outlet_id,
            404
        );
    }
}
