<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enum\UserRoleEnum;
use App\Filament\Resources\ItemPublishLogResource\Pages\ListItemPublishLogs;
use App\Models\ItemPublishLog;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

final class ItemPublishLogResource extends Resource
{
    protected static ?string $model = ItemPublishLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-list';

    protected static ?string $navigationGroup = 'Items';

    protected static ?string $navigationLabel = 'Logs Publication';

    protected static ?int $navigationSort = 10;

    public static function canViewAny(): bool
    {
        return UserRoleEnum::ADMIN === auth()->user()?->role;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('item.reference')
                    ->label('Référence')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('item.brand.name')
                    ->label('Marque')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->sortable()
                    ->default('Système'),
                BadgeColumn::make('action')
                    ->label('Action')
                    ->colors([
                        'primary' => 'form_toggle',
                        'success' => 'bulk_action',
                        'warning' => 'excel_import',
                        'danger' => 'image_import',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'form_toggle' => 'Formulaire',
                        'bulk_action' => 'Action en masse',
                        'excel_import' => 'Import Excel',
                        'image_import' => 'Import images',
                        default => $state,
                    }),
                IconColumn::make('old_value')
                    ->label('Avant')
                    ->boolean(),
                IconColumn::make('new_value')
                    ->label('Après')
                    ->boolean(),
                TextColumn::make('reason')
                    ->label('Raison')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->reason),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')
                    ->label('Type d\'action')
                    ->options([
                        'form_toggle' => 'Formulaire',
                        'bulk_action' => 'Action en masse',
                        'excel_import' => 'Import Excel',
                        'image_import' => 'Import images',
                    ]),
                SelectFilter::make('new_value')
                    ->label('Nouvelle valeur')
                    ->options([
                        '1' => 'Publié',
                        '0' => 'Non publié',
                    ]),
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Du'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItemPublishLogs::route('/'),
        ];
    }

    protected static function getNavigationBadge(): ?string
    {
        return (string) self::getModel()::count();
    }
}
