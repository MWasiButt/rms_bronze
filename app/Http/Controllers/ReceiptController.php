<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\SimpleReceiptPdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        $this->authorizeTenantOrder($request, $order);

        return view('receipts.show', [
            'order' => $order->load(['items.modifiers', 'payments', 'tenant.settings', 'outlet']),
        ]);
    }

    public function pdf(Request $request, Order $order, SimpleReceiptPdf $pdf): Response
    {
        $this->authorizeTenantOrder($request, $order);

        return response($pdf->render($order), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="receipt-'.$order->order_number.'.pdf"',
        ]);
    }

    private function authorizeTenantOrder(Request $request, Order $order): void
    {
        abort_if(
            $order->tenant_id !== $request->user()->tenant_id || $order->outlet_id !== $request->user()->outlet_id,
            404
        );
    }
}
