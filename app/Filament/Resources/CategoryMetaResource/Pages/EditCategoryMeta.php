<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryMetaResource\Pages;

use App\Filament\Resources\CategoryMetaResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditCategoryMeta extends EditRecord
{
    protected static string $resource = CategoryMetaResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
