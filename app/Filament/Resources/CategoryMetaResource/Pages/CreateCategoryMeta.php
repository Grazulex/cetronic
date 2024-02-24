<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryMetaResource\Pages;

use App\Filament\Resources\CategoryMetaResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCategoryMeta extends CreateRecord
{
    protected static string $resource = CategoryMetaResource::class;
}
