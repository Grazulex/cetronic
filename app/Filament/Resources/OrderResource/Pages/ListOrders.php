<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enum\OrderStatusEnum;
use App\Filament\Resources\OrderResource;
use Closure;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

final class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    /**
     * Quick filters shown as pills above the list.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $status = fn(OrderStatusEnum $status): Closure => fn(Builder $query): Builder => $query->where('status', $status->value);

        return [
            'toutes' => Tab::make('Toutes'),
            'ce-mois' => Tab::make('Ce mois')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->where('created_at', '>=', now()->startOfMonth())),
            'ouvertes' => Tab::make('Ouvertes')->modifyQueryUsing($status(OrderStatusEnum::OPEN)),
            'en-preparation' => Tab::make('En préparation')->modifyQueryUsing($status(OrderStatusEnum::IN_PREPARATION)),
            'expediees' => Tab::make('Expédiées')->modifyQueryUsing($status(OrderStatusEnum::SHIPPED)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
