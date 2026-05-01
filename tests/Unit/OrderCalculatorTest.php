<?php

namespace Tests\Unit;

use App\Enums\TaxMode;
use App\Support\OrderCalculator;
use Tests\TestCase;

class OrderCalculatorTest extends TestCase
{
    public function test_exclusive_tax_line_totals_add_tax_to_price(): void
    {
        $totals = app(OrderCalculator::class)->lineTotals(1000, 2, 1600, TaxMode::EXCLUSIVE);

        $this->assertSame([
            'subtotal_cents' => 2000,
            'tax_cents' => 320,
            'total_cents' => 2320,
        ], $totals);
    }

    public function test_inclusive_tax_line_totals_extract_tax_from_price(): void
    {
        $totals = app(OrderCalculator::class)->lineTotals(1160, 1, 1600, TaxMode::INCLUSIVE);

        $this->assertSame([
            'subtotal_cents' => 1000,
            'tax_cents' => 160,
            'total_cents' => 1160,
        ], $totals);
    }
}
