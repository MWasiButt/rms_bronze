<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $order->order_number }}</title>
    <style>
        body { background: #f3f4f6; color: #111827; font-family: Arial, sans-serif; margin: 0; }
        .receipt { background: #fff; margin: 24px auto; max-width: 320px; padding: 18px; box-shadow: 0 10px 30px rgba(15, 23, 42, .12); }
        .center { text-align: center; }
        .muted { color: #6b7280; font-size: 12px; }
        .row { display: flex; justify-content: space-between; gap: 12px; }
        .line { border-top: 1px dashed #9ca3af; margin: 12px 0; }
        .item { margin-bottom: 10px; }
        .strong { font-weight: 700; }
        .actions { display: flex; gap: 8px; justify-content: center; margin: 16px auto; max-width: 320px; }
        .btn { background: #2563eb; border-radius: 6px; color: #fff; padding: 9px 12px; text-decoration: none; }
        .btn.secondary { background: #374151; }
        @media print {
            body { background: #fff; }
            .actions { display: none; }
            .receipt { box-shadow: none; margin: 0; max-width: none; width: 72mm; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <a class="btn" href="javascript:window.print()">Print</a>
        <a class="btn secondary" href="{{ route('receipts.pdf', $order) }}">PDF</a>
        <a class="btn secondary" href="{{ route('service.pos', ['order' => $order->id]) }}">POS</a>
    </div>

    <main class="receipt">
        <div class="center">
            <div class="strong">{{ $order->tenant?->settings?->receipt_header ?: $order->tenant?->name }}</div>
            <div class="muted">{{ $order->outlet?->address }}</div>
            <div class="muted">{{ $order->outlet?->phone }}</div>
        </div>

        <div class="line"></div>
        <div class="row muted"><span>Order</span><span>{{ $order->order_number }}</span></div>
        <div class="row muted"><span>Date</span><span>{{ ($order->paid_at ?? $order->created_at)?->format('Y-m-d H:i') }}</span></div>
        <div class="row muted"><span>Type</span><span>{{ $order->order_type->value }}</span></div>

        <div class="line"></div>
        @foreach ($order->items as $item)
            <div class="item">
                <div class="row">
                    <span>{{ $item->item_name }} x{{ $item->quantity }}</span>
                    <span>{{ number_format($item->line_total_cents / 100, 2) }}</span>
                </div>
                @foreach ($item->modifiers as $modifier)
                    <div class="muted">+ {{ $modifier->modifier_option_name }}</div>
                @endforeach
            </div>
        @endforeach

        <div class="line"></div>
        <div class="row"><span>Subtotal</span><span>{{ number_format($order->subtotal_cents / 100, 2) }}</span></div>
        <div class="row"><span>Tax</span><span>{{ number_format($order->tax_cents / 100, 2) }}</span></div>
        <div class="row"><span>Discount</span><span>{{ number_format($order->discount_cents / 100, 2) }}</span></div>
        <div class="row strong"><span>Total</span><span>{{ number_format($order->total_cents / 100, 2) }}</span></div>
        <div class="row"><span>Paid</span><span>{{ number_format($order->payments->sum('amount_cents') / 100, 2) }}</span></div>
        <div class="row"><span>Method</span><span>{{ $order->payments->map(fn ($payment) => $payment->method->value)->join(', ') }}</span></div>

        <div class="line"></div>
        <div class="center muted">{{ $order->tenant?->settings?->receipt_footer ?: 'Thank you.' }}</div>
    </main>
</body>
</html>
