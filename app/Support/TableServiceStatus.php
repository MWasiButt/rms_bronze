<?php

namespace App\Support;

use App\Enums\OrderStatus;
use App\Models\Order;

class TableServiceStatus
{
    public function label(?Order $order): string
    {
        return match ($order?->status) {
            null => 'Available',
            OrderStatus::OPEN => 'Occupied',
            OrderStatus::SENT_TO_KITCHEN => 'Ordered',
            OrderStatus::READY => 'Ready',
            OrderStatus::SERVED => 'Served',
            default => $order->status->value,
        };
    }
}
