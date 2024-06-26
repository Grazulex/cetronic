<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enum\CartStatusEnum;
use App\Enum\UserRoleEnum;
use App\Models\Cart;
use App\Models\Item;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

final class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Items on line', Item::where('is_published', true)->count())->color('success'),
            Card::make('Items off line', Item::where('is_published', false)->count())->color('warning'),
            Card::make('Carts open', Cart::where('status', CartStatusEnum::OPEN->value)->count())->color('success'),
        ];
    }

    public static function canView(): bool
    {
        return UserRoleEnum::ADMIN === auth()->user()?->role;
    }
}
