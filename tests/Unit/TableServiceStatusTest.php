<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Support\TableServiceStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TableServiceStatusTest extends TestCase
{
    #[DataProvider('statusProvider')]
    public function test_it_maps_order_statuses_to_service_table_labels(?OrderStatus $status, string $label): void
    {
        $order = $status ? new Order(['status' => $status]) : null;

        $this->assertSame($label, app(TableServiceStatus::class)->label($order));
    }

    public static function statusProvider(): array
    {
        return [
            [null, 'Available'],
            [OrderStatus::OPEN, 'Occupied'],
            [OrderStatus::SENT_TO_KITCHEN, 'Ordered'],
            [OrderStatus::READY, 'Ready'],
            [OrderStatus::SERVED, 'Served'],
        ];
    }
}
