<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\UserBrand;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        foreach ($data['brands'] as $brands) {
            foreach ($brands['brand_id'] as $brand) {
                UserBrand::create([
                    'brand_id' => $brand,
                    'category_id' => $brands['category_id'],
                    'reduction' => $brands['discount'],
                    'is_excluded' => $brands['is_excluded'],
                    'user_id' => $record->id,
                ]);
            }
        }

        return $record;
    }
}
