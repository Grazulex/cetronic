<?php

declare(strict_types=1);

namespace App\Enum;

enum CartStatusEnum: string
{
    case OPEN = 'open';
    case SOLD = 'sold';
}
