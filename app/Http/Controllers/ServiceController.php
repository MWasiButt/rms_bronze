<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Events\OrderSentToKitchen;
use App\Events\PrintJobCreated;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\KitchenTicketStatus;
use App\Enums\PrintJobStatus;
use App\Enums\PrintJobType;
use App\Models\DiningTable;
use App\Models\KitchenTicket;
use App\Models\MenuItem;
use App\Models\ModifierOption;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PrintJob;
use App\Support\Audit;
use App\Support\MenuCache;
use App\Support\Money;
use App\Support\OrderCalculator;
use App\Support\TableServiceStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(
        private readonly TableServiceStatus $tableServiceStatus,
        private readonly OrderCalculator $calculator
    )
    {
    }

    public function pos(Request $request, MenuCache $menuCache): View
    {
        $tenantId = $request->user()->tenant_id;
        $outletId = $request->user()->outlet_id;
        $menu = $menuCache->remember($tenantId, $outletId);

        $selectedOrder = $request->integer('order')
            ? $this->tenantOrder($request, $request->integer('order'))->with(['items.modifiers', 'table', 'payments'])->first()
            : $this->activeOrders($request)->with(['items.modifiers', 'table', 'payments'])->latest()->first();

        return view('pos-orders', [
            'tables' => $this->tableStatuses($request),
            'categories' => $menu['categories'],
            'items' => $menu['items'],
            'openOrders' => $this->activeOrders($request)->latest()->get(),
            'selectedOrder' => $selectedOrder,
        ]);
    }

    public function queue(Request $request): View
    {
        $orders = Order::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('outlet_id', $request->user()->outlet_id)
            ->whereIn('status', [
                OrderStatus::OPEN,
                OrderStatus::SENT_TO_KITCHEN,
                OrderStatus::READY,
                OrderStatus::SERVED,
                OrderStatus::VOIDED,
                OrderStatus::PAID,
            ])
            ->with('table')
            ->latest()
            ->get()
            ->groupBy(fn (Order $order) => $order->status->value);

        $paymentSummary = Payment::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('outlet_id', $request->user()->outlet_id)
            ->whereDate('paid_at', today())
            ->selectRaw('method, COUNT(*) as payments_count, SUM(amount_cents) as total_cents')
            ->groupBy('method')
            ->get();

        return view('orders-queue', [
            'columns' => collect([
                OrderStatus::OPEN,
                OrderStatus::SENT_TO_KITCHEN,
                OrderStatus::READY,
                OrderStatus::SERVED,
                OrderStatus::VOIDED,
                OrderStatus::PAID,
            ])->mapWithKeys(fn (OrderStatus $status) => [$status->value => $orders->get($status->value, collect())]),
            'paymentSummary' => $paymentSummary,
        ]);
    }

    public function startOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_type' => ['required', Rule::enum(OrderType::class)],
            'dining_table_id' => ['required_if:order_type,DINE_IN', 'nullable', 'integer'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $type = OrderType::from($validated['order_type']);
        $tableId = null;

        if ($type === OrderType::DINE_IN) {
            $table = DiningTable::query()
                ->where('tenant_id', $request->user()->tenant_id)
                ->where('outlet_id', $request->user()->outlet_id)
                ->where('is_active', true)
                ->findOrFail($validated['dining_table_id']);

            $hasActiveOrder = $table->orders()
                ->whereNotIn('status', [OrderStatus::VOIDED, OrderStatus::PAID])
                ->exists();

            if ($hasActiveOrder) {
                return back()->withErrors(['dining_table_id' => 'This table already has an active order.'])->withInput();
            }

            $tableId = $table->id;
        }

        $createdOrderId = null;

        $createdOrder = null;

        DB::transaction(function () use ($request, $type, $tableId, $validated, &$createdOrderId, &$createdOrder): void {
            $order = Order::create([
                'tenant_id' => $request->user()->tenant_id,
                'outlet_id' => $request->user()->outlet_id,
                'dining_table_id' => $tableId,
                'user_id' => $request->user()->id,
                'order_number' => $this->nextOrderNumber($request),
                'order_type' => $type,
                'status' => OrderStatus::OPEN,
                'guest_count' => $validated['guest_count'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $createdOrderId = $order->id;
            $createdOrder = $order;
        });

        if ($createdOrder) {
            event(new OrderCreated($createdOrder));
        }

        return redirect()->route('service.pos', ['order' => $createdOrderId])->with('status', 'Order started.');
    }

    public function addItem(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeMutableOrder($request, $order);

        $validated = $request->validate([
            'menu_item_id' => ['required', 'integer'],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:999'],
            'modifier_options' => ['array'],
            'modifier_options.*' => ['integer'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $menuItem = MenuItem::query()
            ->with('modifierGroups.options')
            ->where('tenant_id', $request->user()->tenant_id)
            ->where(function ($query) use ($request) {
                $query->whereNull('outlet_id')->orWhere('outlet_id', $request->user()->outlet_id);
            })
            ->where('is_active', true)
            ->findOrFail($validated['menu_item_id']);

        $allowedOptions = $menuItem->modifierGroups
            ->flatMap(function ($group) {
                return $group->options->where('is_active', true)->map(function (ModifierOption $option) use ($group): ModifierOption {
                    return $option->setRelation('modifierGroup', $group);
                });
            })
            ->keyBy('id');

        DB::transaction(function () use ($order, $menuItem, $validated, $allowedOptions): void {
            $item = $order->items()->create([
                'menu_item_id' => $menuItem->id,
                'item_name' => $menuItem->name,
                'sku' => $menuItem->sku,
                'quantity' => $validated['quantity'],
                'unit_price_cents' => $menuItem->price_cents,
                'tax_rate_bps' => $menuItem->tax_rate_bps,
                'tax_mode' => $menuItem->tax_mode,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach (array_unique($validated['modifier_options'] ?? []) as $optionId) {
                $option = $allowedOptions->get((int) $optionId);

                if (! $option instanceof ModifierOption) {
                    continue;
                }

                $item->modifiers()->create([
                    'modifier_group_name' => $option->modifierGroup->name,
                    'modifier_option_name' => $option->name,
                    'price_delta_cents' => $option->price_delta_cents,
                    'quantity' => 1,
                ]);
            }

            $this->calculator->recalculateOrder($order);
        });

        return redirect()->route('service.pos', ['order' => $order->id])->with('status', 'Item added.');
    }

    public function updateItem(Request $request, Order $order, OrderItem $item): RedirectResponse
    {
        $this->authorizeMutableOrder($request, $order);
        $this->authorizeOrderItem($order, $item);

        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:999'],
        ]);

        $item->update(['quantity' => $validated['quantity']]);
        $this->calculator->recalculateOrder($order);

        return redirect()->route('service.pos', ['order' => $order->id])->with('status', 'Quantity updated.');
    }

    public function removeItem(Request $request, Order $order, OrderItem $item): RedirectResponse
    {
        $this->authorizeMutableOrder($request, $order);
        $this->authorizeOrderItem($order, $item);

        $item->delete();
        $this->calculator->recalculateOrder($order);

        return redirect()->route('service.pos', ['order' => $order->id])->with('status', 'Item removed.');
    }

    public function discount(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeMutableOrder($request, $order);

        $validated = $request->validate([
            'discount_type' => ['required', Rule::in(['flat', 'percent'])],
            'discount_value' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ]);

        $base = $order->subtotal_cents + $order->tax_cents;
        $discount = $validated['discount_type'] === 'percent'
            ? (int) round($base * min(100, (float) $validated['discount_value']) / 100)
            : Money::toCents($validated['discount_value']);

        $order->update(['discount_cents' => min($discount, $base)]);
        $this->calculator->recalculateOrder($order);

        return redirect()->route('service.pos', ['order' => $order->id])->with('status', 'Discount updated.');
    }

    public function status(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeTenantOrder($request, $order);

        if (in_array($order->status, [OrderStatus::VOIDED, OrderStatus::PAID], true)) {
            return back()->withErrors(['status' => 'This order is already closed.']);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'payment_method' => ['nullable', Rule::enum(PaymentMethod::class)],
        ]);

        $target = OrderStatus::from($validated['status']);
        $oldStatus = $order->status;

        if ($target === OrderStatus::PAID) {
            return back()->withErrors(['status' => 'Use the payment form to mark this order paid.']);
        }

        DB::transaction(function () use ($request, $order, $target, $validated): void {
            $updates = ['status' => $target];

            if ($target === OrderStatus::SENT_TO_KITCHEN) {
                $updates['sent_to_kitchen_at'] = now();
                $ticket = KitchenTicket::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'tenant_id' => $order->tenant_id,
                        'outlet_id' => $order->outlet_id,
                        'status' => KitchenTicketStatus::PENDING,
                        'fired_at' => now(),
                        'notes' => $order->notes,
                    ]
                );

                $printJob = PrintJob::firstOrCreate(
                    [
                        'tenant_id' => $order->tenant_id,
                        'order_id' => $order->id,
                        'type' => PrintJobType::KOT,
                    ],
                    [
                        'outlet_id' => $order->outlet_id,
                        'kitchen_ticket_id' => $ticket->id,
                        'requested_by_user_id' => $request->user()->id,
                        'channel' => 'agent',
                        'status' => PrintJobStatus::PENDING,
                        'copies' => 1,
                        'payload' => ['order_number' => $order->order_number],
                    ]
                );

                event(new PrintJobCreated($printJob));
            }

            if ($target === OrderStatus::READY) {
                $order->kitchenTicket?->update(['status' => KitchenTicketStatus::READY, 'ready_at' => now()]);
            }

            if ($target === OrderStatus::SERVED) {
                $order->kitchenTicket?->update(['status' => KitchenTicketStatus::SERVED, 'served_at' => now()]);
            }

            if ($target === OrderStatus::VOIDED) {
                $updates['voided_at'] = now();
            }

            $order->update($updates);
        });

        if ($target === OrderStatus::SENT_TO_KITCHEN) {
            event(new OrderSentToKitchen($order->refresh()));
        }

        if ($target === OrderStatus::VOIDED) {
            Audit::record('order.voided', $order->refresh(), [
                'status' => $oldStatus->value,
            ], [
                'status' => $order->status->value,
                'voided_at' => $order->voided_at,
            ], $request);
        }

        return redirect()->route('service.pos', ['order' => $order->id])->with('status', 'Order status updated.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function tableStatuses(Request $request)
    {
        return DiningTable::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('outlet_id', $request->user()->outlet_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (DiningTable $table): array {
                $order = $table->orders()
                    ->whereNotIn('status', [OrderStatus::VOIDED, OrderStatus::PAID])
                    ->latest()
                    ->first();

                return [
                    'table' => $table,
                    'order' => $order,
                    'status' => $this->tableServiceStatus->label($order),
                ];
            });
    }

    private function activeOrders(Request $request)
    {
        return Order::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('outlet_id', $request->user()->outlet_id)
            ->with('table')
            ->whereIn('status', [
                OrderStatus::OPEN,
                OrderStatus::SENT_TO_KITCHEN,
                OrderStatus::READY,
                OrderStatus::SERVED,
            ]);
    }

    private function tenantOrder(Request $request, int $orderId)
    {
        return Order::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('outlet_id', $request->user()->outlet_id)
            ->whereKey($orderId);
    }

    private function authorizeTenantOrder(Request $request, Order $order): void
    {
        abort_if(
            $order->tenant_id !== $request->user()->tenant_id || $order->outlet_id !== $request->user()->outlet_id,
            404
        );
    }

    private function authorizeMutableOrder(Request $request, Order $order): void
    {
        $this->authorizeTenantOrder($request, $order);

        abort_if(in_array($order->status, [OrderStatus::VOIDED, OrderStatus::PAID], true), 422, 'This order is closed.');
    }

    private function authorizeOrderItem(Order $order, OrderItem $item): void
    {
        abort_if($item->order_id !== $order->id, 404);
    }

    private function nextOrderNumber(Request $request): string
    {
        $count = Order::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->whereDate('created_at', today())
            ->lockForUpdate()
            ->count();

        return 'ORD-'.now()->format('Ymd').'-'.str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }
}
