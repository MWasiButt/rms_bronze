<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\Tenant;
use App\Support\SimpleReceiptPdf;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SimpleReceiptPdfTest extends TestCase
{
    public function test_it_renders_a_minimal_pdf_document(): void
    {
        $order = new Order([
            'order_number' => 'ORD-TEST-1',
            'subtotal_cents' => 1000,
            'tax_cents' => 160,
            'discount_cents' => 0,
            'total_cents' => 1160,
        ]);

        $order->setRelation('tenant', new Tenant(['name' => 'Demo']));
        $order->setRelation('outlet', new Outlet(['name' => 'Main']));
        $order->setRelation('items', new Collection());
        $order->setRelation('payments', new Collection());

        $pdf = app(SimpleReceiptPdf::class)->render($order);

        $this->assertStringStartsWith('%PDF-1.4', $pdf);
        $this->assertStringContainsString('%%EOF', $pdf);
    }
}
