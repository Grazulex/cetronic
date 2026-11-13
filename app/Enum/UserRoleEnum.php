<?php

declare(strict_types=1);

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum UserRoleEnum: string implements HasLabel
{
    case ADMIN = 'admin';
    case CUSTOMER = 'customer';
    case AGENT = 'agent';

    public function getLabel(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrateur',
            self::CUSTOMER => 'Client',
            self::AGENT => 'Agent',
        };
    }
}
