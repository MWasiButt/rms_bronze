<?php

namespace Tests\Unit;

use App\Enums\StockMovementType;
use App\Support\InventoryService;
use ReflectionMethod;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    public function test_stock_movement_types_apply_expected_stock_delta(): void
    {
        $method = new ReflectionMethod(InventoryService::class, 'stockDelta');
        $method->setAccessible(true);
        $service = new InventoryService();

        $this->assertSame(5.0, $method->invoke($service, StockMovementType::IN, 5));
        $this->assertSame(-5.0, $method->invoke($service, StockMovementType::OUT, 5));
        $this->assertSame(-5.0, $method->invoke($service, StockMovementType::SALE, 5));
        $this->assertSame(-2.5, $method->invoke($service, StockMovementType::ADJUST, -2.5));
    }
}
