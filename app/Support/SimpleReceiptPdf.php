<?php

namespace App\Support;

use App\Models\Order;

class SimpleReceiptPdf
{
    public function render(Order $order): string
    {
        $order->loadMissing(['items.modifiers', 'payments', 'tenant', 'outlet']);

        $lines = [
            $order->tenant?->name ?? 'Receipt',
            $order->outlet?->name ?? '',
            'Order: '.$order->order_number,
            'Date: '.optional($order->paid_at ?? $order->created_at)->format('Y-m-d H:i'),
            '--------------------------------',
        ];

        foreach ($order->items as $item) {
            $lines[] = $item->item_name.' x'.$item->quantity.' '.number_format($item->line_total_cents / 100, 2);
            foreach ($item->modifiers as $modifier) {
                $lines[] = '  + '.$modifier->modifier_option_name;
            }
        }

        $lines = array_merge($lines, [
            '--------------------------------',
            'Subtotal: '.number_format($order->subtotal_cents / 100, 2),
            'Tax: '.number_format($order->tax_cents / 100, 2),
            'Discount: '.number_format($order->discount_cents / 100, 2),
            'Total: '.number_format($order->total_cents / 100, 2),
            'Paid: '.number_format($order->payments->sum('amount_cents') / 100, 2),
            'Method: '.$order->payments->map(fn ($payment) => $payment->method->value)->join(', '),
            'Thank you',
        ]);

        return $this->pdfFromLines($lines);
    }

    /**
     * Builds a minimal single-page PDF with Helvetica text.
     */
    private function pdfFromLines(array $lines): string
    {
        $content = "BT\n/F1 11 Tf\n50 790 Td\n";

        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $content .= "0 -16 Td\n";
            }

            $content .= '('.$this->escape((string) $line).") Tj\n";
        }

        $content .= "ET\n";

        $objects = [
            '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj',
            '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj',
            '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 226 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj',
            '4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj',
            '5 0 obj << /Length '.strlen($content).' >> stream'."\n".$content.'endstream endobj',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object."\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        return $pdf."trailer << /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF";
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }
}
