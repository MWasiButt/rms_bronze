<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Events\OrderCreated;
use App\Models\DiningTable;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Outlet;
use App\Support\Money;
use App\Support\OrderCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class QrOrderController extends Controller
{
    public function show(Request $request, ?string $table = null): View
    {
        $tableNumber = $table ?? $request->query('table', '12');
        $outlet = $this->resolveOutlet((string) $tableNumber);

        $this->ensureQrOrderingEnabled($outlet);

        return view('customer-order', [
            'table' => $tableNumber,
            'menuItems' => $this->menuItems($outlet),
        ]);
    }

    public function store(Request $request, OrderCalculator $calculator): JsonResponse
    {
        $validated = $request->validate([
            'order_type' => ['required', Rule::in(['dine-in', 'takeaway'])],
            'table' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.itemId' => ['required'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'min:1', 'max:99'],
            'items.*.unitPrice' => ['required', 'numeric', 'min:0'],
            'items.*.details' => ['nullable', 'string', 'max:1000'],
        ]);

        $tableLabel = (string) ($validated['table'] ?? '');
        $outlet = $this->resolveOutlet($tableLabel);

        abort_if(! $outlet, 422, 'No active outlet is available for QR ordering.');
        $this->ensureQrOrderingEnabled($outlet);

        $type = $validated['order_type'] === 'takeaway' ? OrderType::TAKEAWAY : OrderType::DINE_IN;
        $diningTable = $type === OrderType::DINE_IN
            ? $this->resolveDiningTable($outlet, $tableLabel ?: 'QR Table')
            : null;

        $order = null;

        DB::transaction(function () use ($validated, $outlet, $type, $diningTable, $calculator, &$order): void {
            $order = Order::create([
                'tenant_id' => $outlet->tenant_id,
                'outlet_id' => $outlet->id,
                'dining_table_id' => $diningTable?->id,
                'user_id' => null,
                'order_number' => $this->nextOrderNumber((int) $outlet->tenant_id),
                'order_type' => $type,
                'status' => OrderStatus::OPEN,
                'guest_count' => 1,
                'notes' => $this->orderNote($validated),
            ]);

            foreach ($validated['items'] as $payloadItem) {
                $menuItem = MenuItem::query()
                    ->where('tenant_id', $outlet->tenant_id)
                    ->where(function ($query) use ($outlet) {
                        $query->whereNull('outlet_id')->orWhere('outlet_id', $outlet->id);
                    })
                    ->where('is_active', true)
                    ->find($payloadItem['itemId']);

                $order->items()->create([
                    'menu_item_id' => $menuItem?->id,
                    'item_name' => $menuItem?->name ?? $payloadItem['name'],
                    'sku' => $menuItem?->sku,
                    'quantity' => $payloadItem['qty'],
                    'unit_price_cents' => Money::toCents($payloadItem['unitPrice']),
                    'tax_rate_bps' => $menuItem?->tax_rate_bps ?? 0,
                    'tax_mode' => $menuItem?->tax_mode ?? 'EXCLUSIVE',
                    'notes' => $payloadItem['details'] ?? null,
                ]);
            }

            $calculator->recalculateOrder($order);
        });

        try {
            event(new OrderCreated($order->refresh()->load(['items', 'table'])));
        } catch (Throwable $exception) {
            Log::warning('QR order was saved, but realtime broadcast failed.', [
                'order_id' => $order->id,
                'message' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Order placed successfully.',
            'order' => [
                'id' => $order->id,
                'number' => $order->order_number,
                'status' => $order->status->value,
                'total' => $order->total_cents / 100,
            ],
        ], 201);
    }

    private function resolveOutlet(string $tableLabel): ?Outlet
    {
        if ($tableLabel !== '') {
            $table = DiningTable::query()
                ->where('is_active', true)
                ->where(function ($query) use ($tableLabel) {
                    $query->where('name', $tableLabel)->orWhere('code', $tableLabel);
                })
                ->latest()
                ->first();

            if ($table) {
                return Outlet::query()
                    ->where('tenant_id', $table->tenant_id)
                    ->where('is_active', true)
                    ->find($table->outlet_id);
            }
        }

        return Outlet::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }

    private function ensureQrOrderingEnabled(?Outlet $outlet): void
    {
        abort_if(! $outlet, 404);

        $outlet->loadMissing('tenant.settings');

        abort_if(! (bool) $outlet->tenant?->settings?->qr_ordering, 404);
    }

    private function resolveDiningTable(Outlet $outlet, string $tableLabel): DiningTable
    {
        return DiningTable::firstOrCreate(
            [
                'outlet_id' => $outlet->id,
                'name' => $tableLabel,
            ],
            [
                'tenant_id' => $outlet->tenant_id,
                'code' => $tableLabel,
                'is_active' => true,
            ]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function menuItems(?Outlet $outlet): array
    {
        if (! $outlet) {
            return [];
        }

        $items = MenuItem::query()
            ->with('category')
            ->where('tenant_id', $outlet->tenant_id)
            ->where(function ($query) use ($outlet) {
                $query->whereNull('outlet_id')->orWhere('outlet_id', $outlet->id);
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($items->isEmpty()) {
            return [];
        }

        return $items->map(function (MenuItem $item): array {
            $category = $item->category?->name ?? 'Popular';

            return [
                'id' => $item->id,
                'name' => $item->name,
                'desc' => $item->description ?: 'Freshly prepared by our kitchen.',
                'price' => $item->price_cents / 100,
                'category' => str($category)->slug()->toString(),
                'tag' => $category,
                'prepTime' => '10-15 min',
                'customizable' => true,
                'available' => true,
                'img' => asset('assets/images/placeholder-3.jpg'),
            ];
        })->values()->all();
    }

    private function orderNote(array $validated): ?string
    {
        $parts = ['QR order'];

        if (! empty($validated['payment_method'])) {
            $parts[] = 'Payment: '.$validated['payment_method'];
        }

        if (! empty($validated['note'])) {
            $parts[] = 'Note: '.$validated['note'];
        }

        return implode(' | ', $parts);
    }

    private function nextOrderNumber(int $tenantId): string
    {
        $count = Order::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->lockForUpdate()
            ->count();

        return 'ORD-'.now()->format('Ymd').'-'.str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }

    // Bronze money handling uses the shared Money helper for cent conversion.
}
