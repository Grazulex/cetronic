<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enum\UserRoleEnum;
use App\Models\User;
use Closure;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class LatestCustomersToActived extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return UserRoleEnum::ADMIN === auth()->user()?->role;
    }

    protected function getTableQuery(): Builder
    {
        return User::where('is_actif', false)->latest()->limit(10);
    }

    protected function getTableRecordUrlUsing(): Closure
    {
        return fn (Model $record): string => route('filament.resources.users.edit', ['record' => $record]);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('email'),
        ];
    }
}
