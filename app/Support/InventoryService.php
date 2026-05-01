<?php

namespace App\Support;

use App\Enums\StockMovementType;
use App\Models\Order;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function postMovement(
        StockItem $stockItem,
        StockMovementType $type,
        float $quantity,
        ?int $userId,
        ?int $unitCostCents = null,
        ?string $notes = null,
        mixed $reference = null
    ): StockMovement {
        return DB::transaction(function () use ($stockItem, $type, $quantity, $userId, $unitCostCents, $notes, $reference): StockMovement {
            $stockItem = StockItem::query()->whereKey($stockItem->id)->lockForUpdate()->firstOrFail();
            $delta = $this->stockDelta($type, $quantity);

            $stockItem->forceFill([
                'current_stock' => round((float) $stockItem->current_stock + $delta, 3),
            ])->save();

            return StockMovement::create([
                'tenant_id' => $stockItem->tenant_id,
                'outlet_id' => $stockItem->outlet_id,
                'stock_item_id' => $stockItem->id,
                'user_id' => $userId,
                'type' => $type,
                'quantity' => $quantity,
                'unit_cost_cents' => $unitCostCents,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id' => $reference?->id,
                'notes' => $notes,
                'occurred_at' => now(),
            ]);
        });
    }

    public function deductForPaidOrder(Order $order, int $userId): void
    {
        $order->loadMissing('items.menuItem.recipe.items.stockItem');

        foreach ($order->items as $orderItem) {
            $recipe = $orderItem->menuItem?->recipe;

            if (! $recipe || (float) $recipe->yield_quantity <= 0) {
                continue;
            }

            foreach ($recipe->items as $recipeItem) {
                $stockItem = $recipeItem->stockItem;

                if (! $stockItem) {
                    continue;
                }

                $deductQty = round(((float) $recipeItem->quantity * (float) $orderItem->quantity) / (float) $recipe->yield_quantity, 3);

                if ($deductQty <= 0) {
                    continue;
                }

                $this->postMovement(
                    stockItem: $stockItem,
                    type: StockMovementType::SALE,
                    quantity: $deductQty,
                    userId: $userId,
                    notes: 'Auto deduction for '.$order->order_number,
                    reference: $order
                );
            }
        }
    }

    private function stockDelta(StockMovementType $type, float $quantity): float
    {
        return match ($type) {
            StockMovementType::IN => abs($quantity),
            StockMovementType::OUT, StockMovementType::SALE => -abs($quantity),
            StockMovementType::ADJUST => $quantity,
        };
    }
}
