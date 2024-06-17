<?php

declare(strict_types=1);

namespace App\Enum;

use App\Traits\EnumFormat;

enum GenderEnum: string
{
    use EnumFormat;
    case MALE = 'male';
    case FEMALE = 'female';
    case UNISEX = 'unisex';
}
