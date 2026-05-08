<?php

namespace Tests\Feature;

use App\Enums\KitchenTicketStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\PrintJobStatus;
use App\Enums\PrintJobType;
use App\Enums\UserRole;
use App\Models\KitchenTicket;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\PrintJob;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PrintAgentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is required for DB-backed print-agent feature tests.');
        }

        parent::setUp();
    }

    public function test_print_agent_polls_receipt_job_and_marks_it_completed(): void
    {
        [$user, $order] = $this->createPrintableOrder();

        $printJob = PrintJob::create([
            'tenant_id' => $user->tenant_id,
            'outlet_id' => $user->outlet_id,
            'order_id' => $order->id,
            'requested_by_user_id' => $user->id,
            'type' => PrintJobType::RECEIPT,
            'channel' => 'agent',
            'status' => PrintJobStatus::PENDING,
            'copies' => 1,
            'payload' => ['printer' => 'front-counter'],
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/print-agent/jobs/next?type='.PrintJobType::RECEIPT->value)
            ->assertOk()
            ->assertJsonPath('data.id', $printJob->id)
            ->assertJsonPath('data.type', PrintJobType::RECEIPT->value)
            ->assertJsonPath('data.status', PrintJobStatus::PROCESSING->value)
            ->assertJsonPath('data.order.order_number', 'ORD-PRINT-1')
            ->assertJsonPath('data.order.items.0.name', 'Receipt Burger')
            ->assertJsonPath('data.order.items.0.line_total_cents', 120000)
            ->assertJsonPath('data.order.payments.0.method', PaymentMethod::CASH->value)
            ->assertJsonPath('data.order.payments.0.amount_cents', 120000);

        $this->assertDatabaseHas('print_jobs', [
            'id' => $printJob->id,
            'status' => PrintJobStatus::PROCESSING->value,
        ]);

        $this->patchJson('/api/print-agent/jobs/'.$printJob->id, [
            'status' => PrintJobStatus::COMPLETED->value,
            'message' => 'Printed on thermal agent.',
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $printJob->id)
            ->assertJsonPath('data.status', PrintJobStatus::COMPLETED->value);

        $printJob->refresh();

        $this->assertSame(PrintJobStatus::COMPLETED, $printJob->status);
        $this->assertNotNull($printJob->printed_at);
        $this->assertNull($printJob->failed_at);
        $this->assertSame('Printed on thermal agent.', $printJob->payload['agent_message']);
    }

    public function test_print_agent_polls_kot_job_with_kitchen_ticket_payload(): void
    {
        [$user, $order] = $this->createPrintableOrder();

        $ticket = KitchenTicket::create([
            'tenant_id' => $user->tenant_id,
            'outlet_id' => $user->outlet_id,
            'order_id' => $order->id,
            'status' => KitchenTicketStatus::PENDING,
            'fired_at' => now(),
            'notes' => 'Fire immediately',
        ]);

        $printJob = PrintJob::create([
            'tenant_id' => $user->tenant_id,
            'outlet_id' => $user->outlet_id,
            'order_id' => $order->id,
            'kitchen_ticket_id' => $ticket->id,
            'requested_by_user_id' => $user->id,
            'type' => PrintJobType::KOT,
            'channel' => 'agent',
            'status' => PrintJobStatus::PENDING,
            'copies' => 1,
            'payload' => ['station' => 'kitchen'],
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/print-agent/jobs/next?type='.PrintJobType::KOT->value)
            ->assertOk()
            ->assertJsonPath('data.id', $printJob->id)
            ->assertJsonPath('data.type', PrintJobType::KOT->value)
            ->assertJsonPath('data.status', PrintJobStatus::PROCESSING->value)
            ->assertJsonPath('data.order.order_type', OrderType::TAKEAWAY->value)
            ->assertJsonPath('data.order.items.0.name', 'Receipt Burger')
            ->assertJsonPath('data.order.kitchen_ticket.id', $ticket->id)
            ->assertJsonPath('data.order.kitchen_ticket.status', KitchenTicketStatus::PENDING->value)
            ->assertJsonPath('data.order.kitchen_ticket.notes', 'Fire immediately');
    }

    private function createPrintableOrder(): array
    {
        $tenant = Tenant::create([
            'name' => 'Print Cafe',
            'slug' => 'print-cafe',
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

        $user = User::create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'name' => 'Print Agent User',
            'email' => 'print-agent@example.com',
            'password' => 'password',
            'role' => UserRole::CASHIER,
            'is_active' => true,
        ]);

        $order = Order::create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'user_id' => $user->id,
            'order_number' => 'ORD-PRINT-1',
            'order_type' => OrderType::TAKEAWAY,
            'status' => OrderStatus::PAID,
            'guest_count' => 1,
            'subtotal_cents' => 120000,
            'discount_cents' => 0,
            'tax_cents' => 0,
            'total_cents' => 120000,
            'paid_at' => now(),
        ]);

        $order->items()->create([
            'menu_item_id' => null,
            'item_name' => 'Receipt Burger',
            'sku' => 'PRINT-BURGER',
            'quantity' => 2,
            'unit_price_cents' => 60000,
            'tax_rate_bps' => 0,
            'tax_mode' => 'EXCLUSIVE',
            'line_subtotal_cents' => 120000,
            'line_tax_cents' => 0,
            'line_total_cents' => 120000,
            'notes' => 'No sauce',
        ]);

        Payment::create([
            'tenant_id' => $tenant->id,
            'outlet_id' => $outlet->id,
            'order_id' => $order->id,
            'user_id' => $user->id,
            'method' => PaymentMethod::CASH,
            'amount_cents' => 120000,
            'reference' => 'CASH-PRINT',
            'paid_at' => now(),
        ]);

        return [$user, $order];
    }
}
