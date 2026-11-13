<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryMetaResource\Pages;

use App\Filament\Resources\CategoryMetaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditCategoryMeta extends EditRecord
{
    protected static string $resource = CategoryMetaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
