<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Support\ReportDateRange;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function sales(Request $request): View
    {
        $range = ReportDateRange::fromRequest($request);

        $orders = Order::query()
            ->with(['items.menuItem.category', 'payments'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('outlet_id', $request->user()->outlet_id)
            ->where('status', OrderStatus::PAID)
            ->whereBetween('paid_at', [$range->start, $range->end])
            ->orderBy('paid_at')
            ->get();

        $payments = Payment::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('outlet_id', $request->user()->outlet_id)
            ->whereBetween('paid_at', [$range->start, $range->end])
            ->get();

        return view('reports.sales', [
            'range' => $range,
            'orders' => $orders,
            'summary' => $this->summary($orders),
            'dailySales' => $this->dailySales($orders),
            'categoryBreakdown' => $this->categoryBreakdown($orders),
            'itemBreakdown' => $this->itemBreakdown($orders),
            'paymentSummary' => $this->paymentSummary($payments),
        ]);
    }

    private function summary(Collection $orders): array
    {
        $total = (int) $orders->sum('total_cents');
        $count = $orders->count();

        return [
            'order_count' => $count,
            'subtotal_cents' => (int) $orders->sum('subtotal_cents'),
            'tax_cents' => (int) $orders->sum('tax_cents'),
            'discount_cents' => (int) $orders->sum('discount_cents'),
            'total_cents' => $total,
            'average_ticket_cents' => $count > 0 ? (int) round($total / $count) : 0,
        ];
    }

    private function dailySales(Collection $orders): Collection
    {
        return $orders
            ->groupBy(fn (Order $order) => $order->paid_at?->toDateString() ?? 'Unpaid')
            ->map(fn (Collection $orders, string $date) => [
                'date' => $date,
                'orders' => $orders->count(),
                'subtotal_cents' => (int) $orders->sum('subtotal_cents'),
                'tax_cents' => (int) $orders->sum('tax_cents'),
                'discount_cents' => (int) $orders->sum('discount_cents'),
                'total_cents' => (int) $orders->sum('total_cents'),
            ])
            ->values();
    }

    private function categoryBreakdown(Collection $orders): Collection
    {
        return $orders
            ->flatMap->items
            ->groupBy(fn (OrderItem $item) => $item->menuItem?->category?->name ?? 'Uncategorized')
            ->map(fn (Collection $items, string $category) => [
                'category' => $category,
                'quantity' => (float) $items->sum(fn (OrderItem $item) => (float) $item->quantity),
                'total_cents' => (int) $items->sum('line_total_cents'),
            ])
            ->sortByDesc('total_cents')
            ->values();
    }

    private function itemBreakdown(Collection $orders): Collection
    {
        return $orders
            ->flatMap->items
            ->groupBy(fn (OrderItem $item) => $item->item_name.'|'.($item->sku ?? ''))
            ->map(function (Collection $items) {
                $first = $items->first();

                return [
                    'item_name' => $first->item_name,
                    'sku' => $first->sku,
                    'quantity' => (float) $items->sum(fn (OrderItem $item) => (float) $item->quantity),
                    'total_cents' => (int) $items->sum('line_total_cents'),
                ];
            })
            ->sortByDesc('total_cents')
            ->values();
    }

    private function paymentSummary(Collection $payments): Collection
    {
        return $payments
            ->groupBy(fn (Payment $payment) => $payment->method->value)
            ->map(fn (Collection $payments, string $method) => [
                'method' => $method,
                'count' => $payments->count(),
                'total_cents' => (int) $payments->sum('amount_cents'),
            ])
            ->values();
    }
}
