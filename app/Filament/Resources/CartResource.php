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
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

final class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Ventes';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Paniers';

    protected static ?string $modelLabel = 'panier';

    protected static ?string $pluralModelLabel = 'paniers';

    public static function canViewAny(): bool
    {
        return UserRoleEnum::ADMIN === auth()->user()?->role;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Client')
                    ->options(
                        User::all()
                            ->pluck('name', 'id')
                            ->toArray(),
                    )
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(
                        fn(callable $set) => $set('shipping_location_id', null),
                        fn(callable $set) => $set('invoice_location_id', null),
                    )
                    ->required(),
                Forms\Components\Select::make('shipping_location_id')
                    ->label('Adresse de livraison')
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
                    ->label('Adresse de facturation')
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
                    ->label('Client')
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(CartStatusEnum $state): string => CartStatusEnum::OPEN === $state ? 'warning' : 'success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->sortable()
                    ->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->sortable()
                    ->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Filter::make('open')
                    ->query(
                        fn(Builder $query): Builder => $query->where(
                            'status',
                            CartStatusEnum::OPEN->value,
                        ),
                    ),
                Filter::make('close')->query(
                    fn(Builder $query): Builder => $query->where(
                        'status',
                        CartStatusEnum::SOLD->value,
                    ),
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

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
}
