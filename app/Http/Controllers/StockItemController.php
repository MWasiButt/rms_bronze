<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Support\Audit;
use App\Support\InventoryService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockItemController extends Controller
{
    public function index(Request $request): View
    {
        $stockItems = StockItem::withTrashed()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('outlet_id', $request->user()->outlet_id)
            ->orderBy('name')
            ->get();

        return view('inventory.index', [
            'stockItems' => $stockItems,
            'lowStockItems' => $stockItems->filter(fn (StockItem $item) => ! $item->trashed() && (float) $item->current_stock <= (float) $item->reorder_level),
            'movements' => StockMovement::query()
                ->with('stockItem')
                ->where('tenant_id', $request->user()->tenant_id)
                ->where('outlet_id', $request->user()->outlet_id)
                ->latest('occurred_at')
                ->latest()
                ->limit(50)
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('inventory.form', [
            'stockItem' => new StockItem(['is_active' => true, 'unit' => 'unit', 'current_stock' => 0, 'reorder_level' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateStockItem($request);

        $stockItem = StockItem::create([
            ...$validated,
            'tenant_id' => $request->user()->tenant_id,
            'outlet_id' => $request->user()->outlet_id,
            'is_active' => $request->boolean('is_active'),
        ]);

        Audit::record('stock.item.created', $stockItem, [], $stockItem->getAttributes(), $request);

        return redirect()->route('stock-items.index')->with('status', 'Stock item created.');
    }

    public function edit(Request $request, StockItem $stockItem): View
    {
        $this->authorizeStockItem($request, $stockItem);

        return view('inventory.form', ['stockItem' => $stockItem]);
    }

    public function update(Request $request, StockItem $stockItem): RedirectResponse
    {
        $this->authorizeStockItem($request, $stockItem);

        $oldValues = $stockItem->getOriginal();

        $stockItem->update([
            ...$this->validateStockItem($request, $stockItem),
            'is_active' => $request->boolean('is_active'),
        ]);

        Audit::record('stock.item.updated', $stockItem, $oldValues, $stockItem->getChanges(), $request);

        return redirect()->route('stock-items.index')->with('status', 'Stock item updated.');
    }

    public function destroy(Request $request, StockItem $stockItem): RedirectResponse
    {
        $this->authorizeStockItem($request, $stockItem);
        $oldValues = $stockItem->getOriginal();
        $stockItem->delete();
        Audit::record('stock.item.archived', $stockItem, $oldValues, ['deleted_at' => $stockItem->deleted_at], $request);

        return back()->with('status', 'Stock item archived.');
    }

    public function restore(Request $request, int $stockItem): RedirectResponse
    {
        $item = StockItem::withTrashed()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('outlet_id', $request->user()->outlet_id)
            ->findOrFail($stockItem);

        $item->restore();
        Audit::record('stock.item.restored', $item, ['deleted_at' => $item->getOriginal('deleted_at')], ['deleted_at' => null], $request);

        return back()->with('status', 'Stock item restored.');
    }

    public function movement(Request $request, StockItem $stockItem, InventoryService $inventory): RedirectResponse
    {
        $this->authorizeStockItem($request, $stockItem);

        $validated = $request->validate([
            'type' => ['required', Rule::enum(StockMovementType::class)],
            'quantity' => ['required', 'numeric', 'not_in:0', 'min:-999999', 'max:999999'],
            'unit_cost' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $movement = $inventory->postMovement(
            stockItem: $stockItem,
            type: StockMovementType::from($validated['type']),
            quantity: (float) $validated['quantity'],
            userId: $request->user()->id,
            unitCostCents: isset($validated['unit_cost']) ? Money::toCents($validated['unit_cost']) : null,
            notes: $validated['notes'] ?? null
        );

        Audit::record('stock.movement.created', $movement, [], [
            'stock_item_id' => $stockItem->id,
            'type' => $movement->type->value,
            'quantity' => $movement->quantity,
            'unit_cost_cents' => $movement->unit_cost_cents,
        ], $request);

        return back()->with('status', 'Stock movement posted.');
    }

    private function validateStockItem(Request $request, ?StockItem $stockItem = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('stock_items', 'sku')
                    ->where('tenant_id', $request->user()->tenant_id)
                    ->ignore($stockItem?->id),
            ],
            'unit' => ['required', 'string', 'max:20'],
            'current_stock' => ['required', 'numeric', 'min:-999999', 'max:999999'],
            'reorder_level' => ['required', 'numeric', 'min:0', 'max:999999'],
        ]);
    }

    private function authorizeStockItem(Request $request, StockItem $stockItem): void
    {
        abort_if(
            $stockItem->tenant_id !== $request->user()->tenant_id || $stockItem->outlet_id !== $request->user()->outlet_id,
            404
        );
    }
}
