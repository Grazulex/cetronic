<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\UserBrand;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user =  static::getModel()::create($data);

        foreach ($data['brands'] as $brands) {
            foreach ($brands['brand_id'] as $brand) {
                UserBrand::create([
                    'brand_id' => $brand,
                    'category_id' => $brands['category_id'],
                    'reduction'=>$brands['discount'],
                    'is_excluded' => $brands['is_excluded'],
                    'user_id' => $user->id,
                ]);
            }
        }

        return $user;
    }
}
