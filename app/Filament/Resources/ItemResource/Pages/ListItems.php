<?php

declare(strict_types=1);

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

final class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    /**
     * Quick filters shown as pills above the list.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        // Counts follow the list default, which hides deleted products.
        $count = fn(callable $scope): int => $scope(ItemResource::getEloquentQuery()->withoutTrashed())->count();

        $published = fn(Builder $query): Builder => $query->where('is_published', true);
        $unpublished = fn(Builder $query): Builder => $query->where('is_published', false);
        $promo = fn(Builder $query): Builder => $query->where('price_promo', '>', 0);
        $withoutPicture = fn(Builder $query): Builder => $query->doesntHave('media');

        return [
            'tous' => Tab::make('Tous'),
            'en-ligne' => Tab::make('En ligne')->modifyQueryUsing($published)->badge($count($published)),
            'hors-ligne' => Tab::make('Hors ligne')->modifyQueryUsing($unpublished)->badge($count($unpublished)),
            'promo' => Tab::make('En promo')->modifyQueryUsing($promo)->badge($count($promo)),
            'sans-photo' => Tab::make('Sans photo')->modifyQueryUsing($withoutPicture)->badge($count($withoutPicture)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
