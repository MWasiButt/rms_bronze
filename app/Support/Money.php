<?php

namespace App\Support;

use InvalidArgumentException;

class Money
{
    public static function toCents(float|int|string $amount): int
    {
        if (! is_numeric($amount)) {
            throw new InvalidArgumentException('Money amount must be numeric.');
        }

        return (int) round(((float) $amount) * 100);
    }
}
