<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Events\PrintJobCreated;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PrintJobStatus;
use App\Enums\PrintJobType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PrintJob;
use App\Support\Audit;
use App\Support\InventoryService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function store(Request $request, Order $order, InventoryService $inventory): RedirectResponse
    {
        $this->authorizePayableOrder($request, $order);

        $validated = $request->validate([
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'amount' => ['required', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $amountCents = Money::toCents($validated['amount']);
        $paidCents = (int) $order->payments()->sum('amount_cents');
        $remainingCents = max(0, $order->total_cents - $paidCents);

        if ($amountCents !== $remainingCents) {
            return back()->withErrors([
                'amount' => 'Payment must exactly match the remaining balance of '.number_format($remainingCents / 100, 2).'.',
            ])->withInput();
        }

        $payment = null;
        $printJob = null;

        DB::transaction(function () use ($request, $order, $validated, $amountCents, $inventory, &$payment, &$printJob): void {
            $payment = Payment::create([
                'tenant_id' => $order->tenant_id,
                'outlet_id' => $order->outlet_id,
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
                'method' => PaymentMethod::from($validated['method']),
                'amount_cents' => $amountCents,
                'reference' => $validated['reference'] ?? null,
                'paid_at' => now(),
            ]);

            $order->update([
                'status' => OrderStatus::PAID,
                'paid_at' => now(),
            ]);

            $printJob = PrintJob::create([
                'tenant_id' => $order->tenant_id,
                'outlet_id' => $order->outlet_id,
                'order_id' => $order->id,
                'requested_by_user_id' => $request->user()->id,
                'type' => PrintJobType::RECEIPT,
                'channel' => 'agent',
                'status' => PrintJobStatus::PENDING,
                'copies' => 1,
                'payload' => [
                    'order_number' => $order->order_number,
                    'amount_cents' => $amountCents,
                    'method' => $validated['method'],
                ],
            ]);

            $inventory->deductForPaidOrder($order->refresh(), $request->user()->id);
        });

        if ($payment) {
            Audit::record('payment.created', $payment, [], [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'method' => $payment->method->value,
                'amount_cents' => $payment->amount_cents,
            ], $request);
        }

        event(new OrderPaid($order->refresh()));

        if ($printJob) {
            event(new PrintJobCreated($printJob));
        }

        return redirect()->route('receipts.show', $order)->with('status', 'Payment completed and receipt queued.');
    }

    private function authorizePayableOrder(Request $request, Order $order): void
    {
        abort_if(
            $order->tenant_id !== $request->user()->tenant_id || $order->outlet_id !== $request->user()->outlet_id,
            404
        );

        abort_if($order->status === OrderStatus::VOIDED, 422, 'Voided orders cannot be paid.');
        abort_if($order->status === OrderStatus::PAID, 422, 'This order is already paid.');
    }
}
