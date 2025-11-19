<?php

declare(strict_types=1);

namespace App\Filament\Resources\ItemResource\Pages;

use App\Actions\Item\LogItemPublishChangeAction;
use App\Filament\Resources\ItemResource;
use Filament\Pages\Actions\DeleteAction;
use Filament\Pages\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $oldValue = (bool) $record->is_published;
        $newValue = (bool) ($data['is_published'] ?? $oldValue);

        $record->update($data);

        if ($oldValue !== $newValue) {
            app(LogItemPublishChangeAction::class)->handle(
                item: $record,
                oldValue: $oldValue,
                newValue: $newValue,
                action: 'form_toggle',
                userId: auth()->id(),
                reason: 'Modification manuelle via formulaire'
            );
        }

        return $record;
    }
}
