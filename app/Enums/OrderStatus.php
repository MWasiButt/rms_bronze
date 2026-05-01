<?php

namespace App\Enums;

enum OrderStatus: string
{
    case OPEN = 'OPEN';
    case SENT_TO_KITCHEN = 'SENT_TO_KITCHEN';
    case READY = 'READY';
    case SERVED = 'SERVED';
    case VOIDED = 'VOIDED';
    case PAID = 'PAID';
}
