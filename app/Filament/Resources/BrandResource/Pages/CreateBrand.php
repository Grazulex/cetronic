<?php

declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateBrand extends CreateRecord
{
    protected static string $resource = BrandResource::class;
}
