<?php

namespace Tests\Unit;

use App\Enums\KitchenTicketStatus;
use Tests\TestCase;

class KitchenTicketStatusTest extends TestCase
{
    public function test_kitchen_ticket_statuses_match_bronze_kds_flow(): void
    {
        $this->assertSame([
            'PENDING',
            'PREPARING',
            'READY',
            'SERVED',
        ], array_map(fn (KitchenTicketStatus $status) => $status->value, KitchenTicketStatus::cases()));
    }
}
