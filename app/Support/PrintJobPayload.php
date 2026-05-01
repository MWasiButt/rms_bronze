<?php

namespace App\Support;

use App\Models\PrintJob;

class PrintJobPayload
{
    public function build(PrintJob $printJob): array
    {
        $printJob->loadMissing(['order.items.modifiers', 'order.payments', 'order.table', 'kitchenTicket']);

        return [
            'id' => $printJob->id,
            'type' => $printJob->type->value,
            'status' => $printJob->status->value,
            'copies' => $printJob->copies,
            'channel' => $printJob->channel,
            'payload' => $printJob->payload ?? [],
            'order' => $printJob->order ? [
                'id' => $printJob->order->id,
                'order_number' => $printJob->order->order_number,
                'order_type' => $printJob->order->order_type->value,
                'status' => $printJob->order->status->value,
                'table' => $printJob->order->table?->name,
                'subtotal_cents' => $printJob->order->subtotal_cents,
                'discount_cents' => $printJob->order->discount_cents,
                'tax_cents' => $printJob->order->tax_cents,
                'total_cents' => $printJob->order->total_cents,
                'items' => $printJob->order->items->map(fn ($item) => [
                    'name' => $item->item_name,
                    'sku' => $item->sku,
                    'quantity' => (float) $item->quantity,
                    'unit_price_cents' => $item->unit_price_cents,
                    'line_total_cents' => $item->line_total_cents,
                    'modifiers' => $item->modifiers->map(fn ($modifier) => [
                        'group' => $modifier->modifier_group_name,
                        'option' => $modifier->modifier_option_name,
                        'price_delta_cents' => $modifier->price_delta_cents,
                    ])->values(),
                    'notes' => $item->notes,
                ])->values(),
            ] : null,
        ];
    }
}
