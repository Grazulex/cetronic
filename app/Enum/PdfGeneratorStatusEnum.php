<?php

declare(strict_types=1);

namespace App\Enum;

use App\Traits\EnumFormat;

enum PdfGeneratorStatusEnum: string
{
    use EnumFormat;
    case PENDING = 'pending';
    case RUN = 'run';
    case GENERATED = 'generated';
}
