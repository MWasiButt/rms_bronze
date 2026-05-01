<?php

namespace App\Enums;

enum KitchenTicketStatus: string
{
    case PENDING = 'PENDING';
    case PREPARING = 'PREPARING';
    case READY = 'READY';
    case SERVED = 'SERVED';
}
