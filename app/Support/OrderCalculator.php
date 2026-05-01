<?php

namespace App\Support;

use App\Enums\TaxMode;
use App\Models\Order;
use App\Models\OrderItem;

class OrderCalculator
{
    /**
     * @return array{subtotal_cents: int, tax_cents: int, total_cents: int}
     */
    public function lineTotals(int $unitPriceCents, float $quantity, int $taxRateBps, TaxMode $taxMode): array
    {
        $base = (int) round($unitPriceCents * $quantity);

        if ($taxMode === TaxMode::INCLUSIVE) {
            $tax = $taxRateBps > 0 ? (int) round($base * $taxRateBps / (10000 + $taxRateBps)) : 0;

            return [
                'subtotal_cents' => max(0, $base - $tax),
                'tax_cents' => $tax,
                'total_cents' => $base,
            ];
        }

        $tax = (int) round($base * $taxRateBps / 10000);

        return [
            'subtotal_cents' => $base,
            'tax_cents' => $tax,
            'total_cents' => $base + $tax,
        ];
    }

    public function recalculateItem(OrderItem $item): void
    {
        $modifierTotal = $item->modifiers->sum(function ($modifier): int {
            return (int) round($modifier->price_delta_cents * (float) $modifier->quantity);
        });

        $unitPrice = max(0, $item->unit_price_cents + $modifierTotal);
        $totals = $this->lineTotals($unitPrice, (float) $item->quantity, $item->tax_rate_bps, $item->tax_mode);

        $item->forceFill([
            'line_subtotal_cents' => $totals['subtotal_cents'],
            'line_tax_cents' => $totals['tax_cents'],
            'line_total_cents' => $totals['total_cents'],
        ])->save();
    }

    public function recalculateOrder(Order $order): void
    {
        $order->loadMissing('items.modifiers');

        foreach ($order->items as $item) {
            $this->recalculateItem($item);
        }

        $order->refresh()->load('items');

        $subtotal = (int) $order->items->sum('line_subtotal_cents');
        $tax = (int) $order->items->sum('line_tax_cents');
        $discount = min((int) $order->discount_cents, $subtotal + $tax);

        $order->forceFill([
            'subtotal_cents' => $subtotal,
            'tax_cents' => $tax,
            'discount_cents' => $discount,
            'total_cents' => max(0, $subtotal + $tax - $discount),
        ])->save();
    }
}
