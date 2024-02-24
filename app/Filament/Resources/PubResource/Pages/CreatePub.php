<?php

declare(strict_types=1);

namespace App\Filament\Resources\PubResource\Pages;

use App\Filament\Resources\PubResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePub extends CreateRecord
{
    protected static string $resource = PubResource::class;
}
