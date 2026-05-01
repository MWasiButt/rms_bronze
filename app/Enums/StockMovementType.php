<?php

namespace App\Enums;

enum StockMovementType: string
{
    case IN = 'IN';
    case OUT = 'OUT';
    case SALE = 'SALE';
    case ADJUST = 'ADJUST';
}
