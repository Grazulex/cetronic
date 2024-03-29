<?php

declare(strict_types=1);

namespace App\Filament\Resources\CartResource\Pages;

use App\Filament\Resources\CartResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListCarts extends ListRecords
{
    protected static string $resource = CartResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
