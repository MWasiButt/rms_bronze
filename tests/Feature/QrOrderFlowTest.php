<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\TaxMode;
use App\Events\OrderCreated;
use App\Models\DiningTable;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\ProductCategory;
use App\Models\Tenant;
use App\Models\TenantSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class QrOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is required for DB-backed QR order tests.');
        }

        parent::setUp();
    }

    public function test_customer_can_submit_qr_order(): void
    {
        Event::fake();

        $tenant = Tenant::create([
            'name' => 'QR Cafe',
            'slug' => 'qr-cafe',
            'plan_code' => 'bronze',
            'status' => 'active',
            'max_outlets' => 1,
            'max_pos_devices' => 2,
            'max_active_users' => 3,
        ]);

        $outlet = Outlet::create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Outlet',
            'code' => 'MAIN',
            'is_active' => true,
        ]);

        TenantSetting::create([
            'tenant_id' => $tenant->id,
            'currency' => 'PKR',
            'timezone' => 'Asia/Karachi',
            'qr_ordering' => true,
            'delivery' => false,
            'inventory_basic' => true,
            'kds_basic' => true,
            'api_read' => false,
        ]);

        DiningTable::create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'name' => '12',
            'code' => '12',
            'is_active' => true,
        ]);

        $category = ProductCategory::create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'name' => 'Burgers',
            'slug' => 'burgers',
            'is_active' => true,
        ]);

        $item = MenuItem::create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'product_category_id' => $category->id,
            'name' => 'QR Burger',
            'sku' => 'QR-BURGER',
            'price_cents' => 45000,
            'cost_cents' => 0,
            'tax_rate_bps' => 0,
            'tax_mode' => TaxMode::EXCLUSIVE,
            'is_active' => true,
        ]);

        $this->postJson(route('customer.orders.store'), [
            'order_type' => 'dine-in',
            'table' => '12',
            'payment_method' => 'Pay at Counter',
            'note' => 'No onion',
            'items' => [
                [
                    'itemId' => $item->id,
                    'name' => 'QR Burger',
                    'qty' => 2,
                    'unitPrice' => 450,
                    'details' => 'Regular | Spice: Mild',
                ],
            ],
        ])->assertCreated()
            ->assertJsonPath('order.status', OrderStatus::OPEN->value);

        $order = Order::with('items')->firstOrFail();

        $this->assertSame(OrderType::DINE_IN, $order->order_type);
        $this->assertSame(OrderStatus::OPEN, $order->status);
        $this->assertSame(90000, $order->total_cents);
        $this->assertSame('QR Burger', $order->items->first()->item_name);

        Event::assertDispatched(OrderCreated::class);
    }

    public function test_bronze_qr_ordering_is_not_accessible_when_flag_is_disabled(): void
    {
        $tenant = Tenant::create([
            'name' => 'Bronze Cafe',
            'slug' => 'bronze-cafe',
            'plan_code' => 'bronze',
            'status' => 'active',
            'max_outlets' => 1,
            'max_pos_devices' => 2,
            'max_active_users' => 3,
        ]);

        $outlet = Outlet::create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Outlet',
            'code' => 'MAIN',
            'is_active' => true,
        ]);

        TenantSetting::create([
            'tenant_id' => $tenant->id,
            'currency' => 'PKR',
            'timezone' => 'Asia/Karachi',
            'qr_ordering' => false,
            'delivery' => false,
            'inventory_basic' => true,
            'kds_basic' => true,
            'api_read' => false,
        ]);

        DiningTable::create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'name' => '12',
            'code' => '12',
            'is_active' => true,
        ]);

        $this->get(route('customer.table.order', ['table' => '12']))
            ->assertNotFound();
    }
}
