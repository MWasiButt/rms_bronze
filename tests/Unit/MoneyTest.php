<?php

namespace Tests\Unit;

use App\Support\Money;
use InvalidArgumentException;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    public function test_money_values_are_normalized_to_cents(): void
    {
        $this->assertSame(1025, Money::toCents('10.25'));
        $this->assertSame(1000, Money::toCents(10));
        $this->assertSame(1, Money::toCents('0.01'));
    }

    public function test_non_numeric_money_values_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::toCents('ten');
    }
}
