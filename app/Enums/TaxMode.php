<?php

namespace App\Enums;

enum TaxMode: string
{
    case INCLUSIVE = 'INCLUSIVE';
    case EXCLUSIVE = 'EXCLUSIVE';
}
