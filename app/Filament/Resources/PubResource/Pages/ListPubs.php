<?php

declare(strict_types=1);

namespace App\Filament\Resources\PubResource\Pages;

use App\Filament\Resources\PubResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPubs extends ListRecords
{
    protected static string $resource = PubResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
