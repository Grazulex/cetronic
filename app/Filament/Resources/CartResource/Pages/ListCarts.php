<?php

declare(strict_types=1);

namespace App\Filament\Resources\CartResource\Pages;

use App\Enum\CartStatusEnum;
use App\Filament\Resources\CartResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

final class ListCarts extends ListRecords
{
    protected static string $resource = CartResource::class;

    /**
     * Quick filters shown as pills above the list.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'tous' => Tab::make('Tous'),
            'ouverts' => Tab::make('Ouverts')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->where('status', CartStatusEnum::OPEN->value)),
            'vendus' => Tab::make('Vendus')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->where('status', CartStatusEnum::SOLD->value)),
        ];
    }

    /** Open carts first, as the list did before the tabs. */
    public function getDefaultActiveTab(): string|int|null
    {
        return 'ouverts';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
