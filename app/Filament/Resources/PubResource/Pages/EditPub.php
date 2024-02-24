<?php

declare(strict_types=1);

namespace App\Filament\Resources\PubResource\Pages;

use App\Filament\Resources\PubResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPub extends EditRecord
{
    protected static string $resource = PubResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
