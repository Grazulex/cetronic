<?php

declare(strict_types=1);

namespace App\Enum;

enum PriceTypeEnum: string
{
    case DEFAULT = 'default';
    case B2B = 'b2b';
    case SPECIAL_1 = 'special_1';
    case SPECIAL_2 = 'special_2';
    case SPECIAL_3 = 'special_3';

    case FIX = 'fix';
}
