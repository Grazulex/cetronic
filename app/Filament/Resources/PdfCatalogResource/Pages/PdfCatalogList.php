<?php

declare(strict_types=1);

namespace App\Filament\Resources\PdfCatalogResource\Pages;

use App\Filament\Resources\PdfCatalogResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

final class PdfCatalogList extends ListRecords
{
    protected static string $resource = PdfCatalogResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
