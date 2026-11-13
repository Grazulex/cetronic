<?php

declare(strict_types=1);

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum CartStatusEnum: string implements HasLabel
{
    case OPEN = 'open';
    case SOLD = 'sold';

    public function getLabel(): string
    {
        return match ($this) {
            self::OPEN => 'Ouvert',
            self::SOLD => 'Vendu',
        };
    }
}
