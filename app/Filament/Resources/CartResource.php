<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enum\CartStatusEnum;
use App\Enum\LocationTypeEnum;
use App\Enum\UserRoleEnum;
use App\Filament\Resources\CartResource\Pages;
use App\Filament\Resources\CartResource\RelationManagers\ItemsRelationManager;
use App\Models\Cart;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

final class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Orders';

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === UserRoleEnum::ADMIN;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->options(
                        User::all()
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(
                        fn (callable $set) => $set('shipping_location_id', null),
                        fn (callable $set) => $set('invoice_location_id', null)
                    )
                    ->required(),
                Forms\Components\Select::make('shipping_location_id')
                    ->label('Shipping location')
                    ->options(function (callable $get) {
                        $user = User::find($get('user_id'));
                        if ( ! $user) {
                            return [];
                        }

                        return $user->locations
                            ->where('type', LocationTypeEnum::SHIPPING)
                            ->pluck('full_name', 'id')
                            ->toArray();
                    }),
                Forms\Components\Select::make('invoice_location_id')
                    ->label('Invoice location')
                    ->options(function (callable $get) {
                        $user = User::find($get('user_id'));
                        if ( ! $user) {
                            return [];
                        }

                        return $user->locations
                            ->where('type', LocationTypeEnum::INVOICE)
                            ->pluck('full_name', 'id')
                            ->toArray();
                    }),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->sortable()
                    ->dateTime(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Filter::make('open')
                    ->query(
                        fn (Builder $query): Builder => $query->where(
                            'status',
                            CartStatusEnum::OPEN->value
                        )
                    )
                    ->default(),
                Filter::make('close')->query(
                    fn (Builder $query): Builder => $query->where(
                        'status',
                        CartStatusEnum::SOLD->value
                    )
                ),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getRelations(): array
    {
        return [ItemsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarts::route('/'),
            'create' => Pages\CreateCart::route('/create'),
            'edit' => Pages\EditCart::route('/{record}/edit'),
        ];
    }

    protected static function getNavigationBadge(): ?string
    {
        return (string)static::getModel()::count();
    }
}
