<?php

declare(strict_types=1);

namespace App\Filament\Resources\PdfCatalogResource\Pages;

use App\Enum\PdfGeneratorStatusEnum;
use App\Filament\Resources\PdfCatalogResource;
use App\Models\PdfCatalog;
use Filament\Resources\Pages\CreateRecord;

final class PdfCatalogCreate extends CreateRecord
{
    protected static string $resource = PdfCatalogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['conditions'] = [
            PdfCatalog::CONDITION_BRAND => $data[PdfCatalog::CONDITION_BRAND] ?? null,
            PdfCatalog::CONDITION_CATEGORY => $data[PdfCatalog::CONDITION_CATEGORY] ?? null,
            PdfCatalog::CONDITION_TYPE => $data[PdfCatalog::CONDITION_TYPE] ?? null,
            PdfCatalog::CONDITION_GENDER => $data[PdfCatalog::CONDITION_GENDER] ?? null
        ];
        $data['status'] = PdfGeneratorStatusEnum::PENDING;
        return $data;
    }
}
