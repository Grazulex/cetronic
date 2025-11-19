<?php

declare(strict_types=1);

namespace App\Filament\Resources\ItemResource\Pages;

use App\Actions\Item\LogItemPublishChangeAction;
use App\Filament\Resources\ItemResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $record = static::getModel()::create($data);

        // Log si is_published est différent de la valeur par défaut (true)
        $newValue = (bool) ($data['is_published'] ?? true);
        if ($newValue === false) {
            app(LogItemPublishChangeAction::class)->handle(
                item: $record,
                oldValue: true, // valeur par défaut
                newValue: false,
                action: 'form_toggle',
                userId: auth()->id(),
                reason: 'Création avec is_published désactivé'
            );
        }

        return $record;
    }
}
