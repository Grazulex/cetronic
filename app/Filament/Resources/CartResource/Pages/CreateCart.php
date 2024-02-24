<?php

declare(strict_types=1);

namespace App\Filament\Resources\CartResource\Pages;

use App\Filament\Resources\CartResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCart extends CreateRecord
{
    protected static string $resource = CartResource::class;
}
