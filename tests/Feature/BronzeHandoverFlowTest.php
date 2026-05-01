<?php

namespace Tests\Feature;

use App\Enums\KitchenTicketStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\TaxMode;
use App\Enums\UserRole;
use App\Models\KitchenTicket;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\PrintJob;
use App\Models\ProductCategory;
use App\Models\StockItem;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BronzeHandoverFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is required for DB-backed handover feature tests.');
        }

        parent::setUp();
    }

    public function test_demo_login_sets_tenant_context(): void
    {
        $this->seed();

        $response = $this->post(route('login.attempt'), [
            'email' => 'mwasi5276@gmail.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('tenant_id');
        $response->assertSessionHas('outlet_id');
        $this->assertAuthenticated();
    }

    public function test_bronze_active_user_limit_blocks_extra_staff(): void
    {
        $this->seed();

        $owner = User::where('email', 'mwasi5276@gmail.com')->firstOrFail();

        $response = $this->actingAs($owner)->post(route('staff.store'), [
            'name' => 'Extra Active Staff',
            'email' => 'extra.staff@example.com',
            'role' => UserRole::CASHIER->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => '1',
        ]);

        $response->assertSessionHasErrors('plan');
    }

    public function test_owner_cannot_access_menu_items_from_another_tenant(): void
    {
        $this->seed();

        $owner = User::where('email', 'mwasi5276@gmail.com')->firstOrFail();
        $otherTenant = Tenant::create([
            'name' => 'Other Restaurant',
            'slug' => 'other-restaurant',
            'plan_code' => 'bronze',
            'status' => 'active',
            'max_outlets' => 1,
            'max_pos_devices' => 2,
            'max_active_users' => 3,
        ]);
        $otherOutlet = Outlet::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Outlet',
            'code' => 'OTHER',
            'is_active' => true,
        ]);
        $otherCategory = ProductCategory::create([
            'tenant_id' => $otherTenant->id,
            'outlet_id' => $otherOutlet->id,
            'name' => 'Private Category',
            'slug' => 'private-category',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $otherItem = MenuItem::create([
            'tenant_id' => $otherTenant->id,
            'outlet_id' => $otherOutlet->id,
            'product_category_id' => $otherCategory->id,
            'name' => 'Private Item',
            'sku' => 'PRIVATE-ITEM',
            'price_cents' => 1000,
            'cost_cents' => 500,
            'tax_rate_bps' => 0,
            'tax_mode' => TaxMode::EXCLUSIVE,
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->get(route('menu-items.edit', $otherItem))
            ->assertNotFound();
    }

    public function test_owner_can_create_menu_category_and_item(): void
    {
        $this->seed();

        $owner = User::where('email', 'mwasi5276@gmail.com')->firstOrFail();

        $this->actingAs($owner)->post(route('menu-categories.store'), [
            'name' => 'QA Specials',
            'sort_order' => 9,
            'is_active' => '1',
        ])->assertRedirect(route('menu-categories.index'));

        $category = ProductCategory::where('tenant_id', $owner->tenant_id)
            ->where('name', 'QA Specials')
            ->firstOrFail();

        $this->actingAs($owner)->post(route('menu-items.store'), [
            'product_category_id' => $category->id,
            'name' => 'QA Test Meal',
            'sku' => 'QA-MEAL',
            'description' => 'Created by the handover QA test.',
            'price' => '12.50',
            'cost' => '6.00',
            'tax_rate' => '16',
            'tax_mode' => TaxMode::EXCLUSIVE->value,
            'is_active' => '1',
            'modifier_groups' => [],
        ])->assertRedirect(route('menu-items.index'));

        $this->assertDatabaseHas('menu_items', [
            'tenant_id' => $owner->tenant_id,
            'sku' => 'QA-MEAL',
            'price_cents' => 1250,
        ]);
    }

    public function test_order_kot_payment_receipt_stock_and_reports_flow(): void
    {
        Event::fake();
        $this->seed();

        $cashier = User::where('email', 'cashier@example.com')->firstOrFail();
        $kitchen = User::where('email', 'kitchen@example.com')->firstOrFail();
        $menuItem = MenuItem::where('sku', 'MI-ZINGER')->firstOrFail();
        $bunStock = StockItem::where('sku', 'STK-BUN')->firstOrFail();
        $startingBuns = (float) $bunStock->current_stock;

        $this->actingAs($cashier)->post(route('orders.start'), [
            'order_type' => OrderType::TAKEAWAY->value,
            'guest_count' => 1,
            'notes' => 'QA handover order',
        ])->assertRedirect();

        $order = Order::where('tenant_id', $cashier->tenant_id)
            ->where('notes', 'QA handover order')
            ->latest()
            ->firstOrFail();

        $this->actingAs($cashier)->post(route('orders.items.store', $order), [
            'menu_item_id' => $menuItem->id,
            'quantity' => 1,
            'modifier_options' => [],
        ])->assertRedirect(route('service.pos', ['order' => $order->id]));

        $this->actingAs($cashier)->patch(route('orders.status', $order), [
            'status' => OrderStatus::SENT_TO_KITCHEN->value,
        ])->assertRedirect(route('service.pos', ['order' => $order->id]));

        $ticket = KitchenTicket::where('order_id', $order->id)->firstOrFail();

        $this->actingAs($kitchen)->patch(route('kitchen.tickets.update', $ticket), [
            'status' => KitchenTicketStatus::PREPARING->value,
        ])->assertRedirect();
        $this->actingAs($kitchen)->patch(route('kitchen.tickets.update', $ticket), [
            'status' => KitchenTicketStatus::READY->value,
        ])->assertRedirect();
        $this->actingAs($kitchen)->patch(route('kitchen.tickets.update', $ticket), [
            'status' => KitchenTicketStatus::SERVED->value,
        ])->assertRedirect();

        $order->refresh();
        $this->assertSame(OrderStatus::SERVED, $order->status);

        $this->actingAs($cashier)->post(route('orders.payments.store', $order), [
            'method' => PaymentMethod::CASH->value,
            'amount' => number_format($order->total_cents / 100, 2, '.', ''),
            'reference' => 'QA-CASH',
        ])->assertRedirect(route('receipts.show', $order));

        $order->refresh();
        $this->assertSame(OrderStatus::PAID, $order->status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount_cents' => $order->total_cents,
            'method' => PaymentMethod::CASH->value,
        ]);
        $this->assertTrue(PrintJob::where('order_id', $order->id)->exists());
        $this->assertSame($startingBuns - 1, (float) $bunStock->refresh()->current_stock);

        $this->actingAs($cashier)
            ->get(route('receipts.show', $order))
            ->assertOk()
            ->assertSee($order->order_number);

        $this->actingAs($cashier)
            ->get(route('receipts.pdf', $order))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs(User::where('email', 'mwasi5276@gmail.com')->firstOrFail())
            ->get(route('reports.sales', [
                'from' => now()->toDateString(),
                'to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Payment Method Summary');
    }
}
