<?php

namespace App\Enums;

enum UserRole: string
{
    case OWNER = 'OWNER';
    case CASHIER = 'CASHIER';
    case KITCHEN = 'KITCHEN';
}
