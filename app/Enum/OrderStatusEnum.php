<?php

declare(strict_types=1);

namespace App\Enum;

enum OrderStatusEnum: string
{
    case OPEN = 'open';
    case IN_PREPARATION = 'in_preparation';
    case SHIPPED = 'shipped';
}
