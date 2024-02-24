<?php

declare(strict_types=1);

namespace App\Enum;

enum LocationTypeEnum: string
{
    case SHIPPING = 'shipping';
    case INVOICE = 'invoice';
}
