<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enum\OrderStatusEnum;
use App\Enum\UserRoleEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ItemsRelationManager;
use App\Models\Order;
use App\Services\OrderService;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

final class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Orders';

    public static function canViewAny(): bool
    {
        return UserRoleEnum::ADMIN === auth()->user()?->role;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Main fields')
                ->schema([
                    Forms\Components\TextInput::make('reference')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Radio::make('status')
                        ->options(OrderStatusEnum::class)
                        ->required(),
                ])->columns(2),
            Forms\Components\Section::make('Tracking')
                ->schema([
                    Forms\Components\TextInput::make('tracking_number')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('tracking_url')
                        ->maxLength(255),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->sortable(),
                Tables\Columns\TextColumn::make('total_price')->suffix('€')->alignRight(),
                Tables\Columns\BadgeColumn::make('items_count')->counts(
                    'items',
                )->alignRight(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Filter::make('open')
                    ->query(
                        fn(Builder $query): Builder => $query->where(
                            'status',
                            OrderStatusEnum::OPEN->value,
                        ),
                    ),
                Filter::make('in preparation')->query(
                    fn(Builder $query): Builder => $query->where(
                        'status',
                        OrderStatusEnum::IN_PREPARATION->value,
                    ),
                ),
                Filter::make('shipped')->query(
                    fn(Builder $query): Builder => $query->where(
                        'status',
                        OrderStatusEnum::SHIPPED->value,
                    ),
                ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('exportPDF')
                    ->label(__('Export PDF'))
                    ->action(function ($record) {
                        return response()->streamDownload(
                            function () use (
                                $record
                            ): void {
                                $orderService = new OrderService();
                                echo $orderService->getPdf($record);
                            },
                            'order_' . $record->reference . '.pdf',
                        );
                    })
                    ->tooltip(__('Export'))
                    ->icon('heroicon-s-download')
                    ->color('primary'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\BulkAction::make('shippend')
                    ->label(__('Shipped selection'))
                    ->icon('heroicon-s-check-circle')
                    ->action(function (Collection $records): void {
                        foreach ($records as $record) {
                            $record->status = OrderStatusEnum::SHIPPED->value;
                            $record->save();
                        }
                    })
                    ->deselectRecordsAfterCompletion(),

            ]);
    }

    public static function getRelations(): array
    {
        return [ItemsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    protected static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
}
