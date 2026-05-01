<?php

namespace Tests\Unit;

use App\Enums\KitchenTicketStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PrintJobStatus;
use App\Enums\PrintJobType;
use App\Events\KitchenTicketStatusChanged;
use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Events\OrderSentToKitchen;
use App\Events\PrintJobCreated;
use App\Models\KitchenTicket;
use App\Models\Order;
use App\Models\PrintJob;
use Tests\TestCase;

class RealtimeEventsTest extends TestCase
{
    public function test_order_events_use_expected_broadcast_names(): void
    {
        $order = new Order([
            'tenant_id' => 7,
            'order_number' => 'ORD-1',
            'order_type' => OrderType::DINE_IN,
            'status' => OrderStatus::OPEN,
            'total_cents' => 1000,
        ]);
        $order->id = 10;

        $this->assertSame('order.created', (new OrderCreated($order))->broadcastAs());
        $this->assertSame('order.sent_to_kitchen', (new OrderSentToKitchen($order))->broadcastAs());
        $this->assertSame('order.paid', (new OrderPaid($order))->broadcastAs());
    }

    public function test_kitchen_and_print_events_use_expected_broadcast_names(): void
    {
        $ticket = new KitchenTicket([
            'tenant_id' => 7,
            'order_id' => 10,
            'status' => KitchenTicketStatus::READY,
        ]);
        $ticket->id = 20;

        $printJob = new PrintJob([
            'tenant_id' => 7,
            'type' => PrintJobType::KOT,
            'status' => PrintJobStatus::PENDING,
            'order_id' => 10,
        ]);
        $printJob->id = 30;

        $this->assertSame('kitchen.ticket.status_changed', (new KitchenTicketStatusChanged($ticket))->broadcastAs());
        $this->assertSame('print.job.created', (new PrintJobCreated($printJob))->broadcastAs());
    }
}
