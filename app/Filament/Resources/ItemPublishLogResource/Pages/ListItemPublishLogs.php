<?php

declare(strict_types=1);

namespace App\Filament\Resources\ItemPublishLogResource\Pages;

use App\Filament\Resources\ItemPublishLogResource;
use Filament\Resources\Pages\ListRecords;

final class ListItemPublishLogs extends ListRecords
{
    protected static string $resource = ItemPublishLogResource::class;

    protected function getActions(): array
    {
        return [];
    }
}
