<?php

declare(strict_types=1);

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum OrderStatusEnum: string implements HasLabel
{
    case OPEN = 'open';
    case IN_PREPARATION = 'in_preparation';
    case SHIPPED = 'shipped';

    public function getLabel(): string
    {
        return match ($this) {
            self::OPEN => 'Ouverte',
            self::IN_PREPARATION => 'En préparation',
            self::SHIPPED => 'Expédiée',
        };
    }
}
