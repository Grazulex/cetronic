<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enum\CartStatusEnum;
use App\Enum\UserRoleEnum;
use App\Models\Cart;
use Closure;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class LatestCartsOpen extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->role == UserRoleEnum::ADMIN;
    }

    protected function getTableQuery(): Builder
    {
        return Cart::where('status', CartStatusEnum::OPEN->value)->latest()->limit(10);
    }

    protected function getTableRecordUrlUsing(): Closure
    {
        return fn (Model $record): string => route('filament.resources.carts.edit', ['record' => $record]);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('user.name'),
            Tables\Columns\TextColumn::make('created_at'),
        ];
    }
}
