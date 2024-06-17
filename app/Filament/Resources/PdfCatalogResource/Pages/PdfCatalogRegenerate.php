<?php

declare(strict_types=1);

namespace App\Filament\Resources\PdfCatalogResource\Pages;

use App\Filament\Resources\PdfCatalogResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\Page;
use App\Models\PdfCatalog;

final class PdfCatalogRegenerate extends Page
{
    protected static string $resource = PdfCatalogResource::class;
    protected static string $view = 'filament.pages.pdf-generate';
}
