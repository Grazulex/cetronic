<?php

declare(strict_types=1);

namespace App\Filament\Resources\CartResource\Pages;

use App\Filament\Resources\CartResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditCart extends EditRecord
{
    protected static string $resource = CartResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
