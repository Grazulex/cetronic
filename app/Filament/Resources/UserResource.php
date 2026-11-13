<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enum\UserRoleEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\BrandsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\DisablesRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\DiscountsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\LocationsRelationManager;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomerType;
use App\Models\User;
use App\Services\UserService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Clients';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Clients';

    protected static ?string $modelLabel = 'client';

    protected static ?string $pluralModelLabel = 'clients';

    public static function canViewAny(): bool
    {
        return UserRoleEnum::ADMIN === auth()->user()?->role;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('customerType')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('external_reference')
                    ->label('Réf. externe')
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->weight(\Filament\Support\Enums\FontWeight::SemiBold)
                    ->description(fn(User $record): string => (string) $record->email)
                    ->searchable(['name', 'email'])
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('role')
                    ->label('Type de compte')
                    ->badge()
                    ->color(fn(UserRoleEnum $state): string => match ($state) {
                        UserRoleEnum::ADMIN => 'gray',
                        UserRoleEnum::AGENT => 'info',
                        UserRoleEnum::CUSTOMER => 'success',
                    })
                    ->sortable(),
                TextColumn::make('customerType.name')
                    ->label('Type de client')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_actif')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('orders_count')
                    ->label('Commandes')
                    ->counts('orders')
                    ->alignRight()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('logged_at')
                    ->label('Dernière connexion')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('role')
                    ->label('Type de compte')
                    ->options([
                        'admin' => 'Admin',
                        'agent' => 'Agent', 
                        'customer' => 'Client',
                    ]),
                Tables\Filters\SelectFilter::make('customer_type_id')
                    ->label('Type de client')
                    ->options(CustomerType::where('is_active', true)->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('agent_id')
                    ->label('Agent')
                    ->options(User::where('role', 'agent')->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('is_actif')
                    ->label('Compte actif')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs')
                    ->falseLabel('Inactifs'),
                Tables\Filters\TernaryFilter::make('is_blocked')
                    ->label('Compte bloqué')
                    ->placeholder('Tous')
                    ->trueLabel('Bloqués')
                    ->falseLabel('Non bloqués'),
                Tables\Filters\SelectFilter::make('language')
                    ->label('Langue')
                    ->options([
                        'fr' => 'Français',
                        'en' => 'English',
                        'nl' => 'Nederlands',
                    ]),
                Tables\Filters\SelectFilter::make('invoice_country')
                    ->label('Pays de facturation')
                    ->options([
                        'BEL' => 'Belgique',
                        'FRA' => 'France', 
                        'NLD' => 'Pays-Bas',
                        'DEU' => 'Allemagne',
                        'LUX' => 'Luxembourg',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'],
                                fn (Builder $query, $country): Builder => $query->whereHas('locations', function (Builder $query) use ($country) {
                                    $query->where('type', 'invoice')->where('country', $country);
                                }),
                            );
                    }),
                Tables\Filters\Filter::make('invoice_zip')
                    ->form([
                        Forms\Components\TextInput::make('zip')
                            ->label('Code postal de facturation')
                            ->placeholder('Ex: 1000, 7500, etc.')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['zip'],
                                fn (Builder $query, $zip): Builder => $query->whereHas('locations', function (Builder $query) use ($zip) {
                                    $query->where('type', 'invoice')->where('zip', 'like', "%{$zip}%");
                                }),
                            );
                    }),
                Tables\Filters\Filter::make('invoice_city')
                    ->form([
                        Forms\Components\TextInput::make('city')
                            ->label('Ville de facturation')
                            ->placeholder('Ex: Bruxelles, Paris, etc.')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['city'],
                                fn (Builder $query, $city): Builder => $query->whereHas('locations', function (Builder $query) use ($city) {
                                    $query->where('type', 'invoice')->where('city', 'like', "%{$city}%");
                                }),
                            );
                    }),
                Tables\Filters\Filter::make('has_orders')
                    ->label('A des commandes')
                    ->query(fn (Builder $query): Builder => $query->has('orders')),
                Tables\Filters\Filter::make('no_orders')
                    ->label('Sans commande')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('orders')),
                Tables\Filters\Filter::make('franco_above_100')
                    ->label('Franco > 100€')
                    ->query(fn (Builder $query): Builder => $query->where('franco', '>', 10000)), // en centimes
                Tables\Filters\Filter::make('created_last_month')
                    ->label('Créé le mois dernier')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->subMonth()->startOfMonth(),
                        now()->subMonth()->endOfMonth(),
                    ])),
                Tables\Filters\Filter::make('never_logged')
                    ->label('Jamais connecté')
                    ->query(fn (Builder $query): Builder => $query->whereNull('logged_at')),
            ])
            ->actions(actions: [
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Modifier'),
                Tables\Actions\Action::make('stats')
                    ->label('Statistiques')
                    ->iconButton()
                    ->icon('heroicon-s-chart-bar')
                    ->color(fn ($record) => ($record->role === UserRoleEnum::CUSTOMER && $record->orders_count > 0) ? 'success' : 'gray')
                    ->url(fn ($record) => ($record->role === UserRoleEnum::CUSTOMER && $record->orders_count > 0) 
                        ? UserResource::getUrl('customer-stats', ['record' => $record]) 
                        : null)
                    ->disabled(fn ($record) => $record->role !== UserRoleEnum::CUSTOMER || $record->orders_count == 0)
                    ->tooltip(fn ($record) => $record->role !== UserRoleEnum::CUSTOMER 
                        ? 'Statistiques disponibles uniquement pour les clients' 
                        : ($record->orders_count == 0 ? 'Aucune commande trouvée' : 'Voir les statistiques')),
                Tables\Actions\ActionGroup::make([
                Tables\Actions\Action::make('Login')
                    ->label('Se connecter en tant que')
                    ->action(function ($record) {
                        $user = User::find($record->id);
                        auth()->login($user, $remember = true);
                        return redirect()->route('home');
                    })
                    ->icon('heroicon-s-key')
                    ->color('primary'),
                Tables\Actions\Action::make('Print')
                    ->label('Fiche PDF')
                    ->action(function ($record) {
                        return response()->streamDownload(
                            function () use (
                                $record
                            ): void {
                                $userService = new UserService($record);
                                echo $userService->getPdf();
                            },
                            'order_' . $record->id . '.pdf',
                        );
                    })
                    ->icon('heroicon-s-printer')
                    ->color('primary'),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                ]),
            ])
            ->bulkActions(actions: [
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkAction::make('resetPassword')
                    ->action(function (Collection $records, array $data): void {
                        foreach ($records as $record) {
                            $record->password = Hash::make($data['password']);
                            $record->save();
                        }
                    })
                    ->icon('heroicon-o-key')
                    ->form([
                        Forms\Components\TextInput::make('password')
                            ->label('New password')
                            ->required(),
                    ]),
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(['default' => 1, 'lg' => 3])->schema([
                    Forms\Components\Group::make([
                        Forms\Components\Section::make('Informations')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->label('E-mail')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('external_reference')
                                    ->label('Référence externe')
                                    ->maxLength(255),
                                Forms\Components\Select::make('language')
                                    ->label('Langue')
                                    ->options(['fr' => 'Français', 'en' => 'English', 'nl' => 'Nederlands'])
                                    ->searchable(),
                                Forms\Components\Select::make('customer_type_id')
                                    ->label('Type de client')
                                    ->options(CustomerType::where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->nullable(),
                                Forms\Components\Select::make('agent_id')
                                    ->label('Agent')
                                    ->options(User::where('role', 'agent')->pluck('name', 'id'))
                                    ->searchable(),
                            ])->columns(3),
                        Forms\Components\Section::make('Livraison')->schema([
                            Forms\Components\TextInput::make('franco')
                                ->label('Franco de port')
                                ->helperText('Montant de commande à partir duquel la livraison est gratuite')
                                ->numeric()
                                ->suffix('€'),
                            Forms\Components\TextInput::make('shipping_price')
                                ->label('Frais de port')
                                ->helperText('Frais de port fixes pour chaque commande')
                                ->numeric()
                                ->suffix('€'),
                        ])->columns(2),
                        Forms\Components\Section::make('Remises par marque')
                            ->description('Création rapide de plusieurs remises en une fois')
                            ->collapsible()
                            ->schema([
                                Forms\Components\Repeater::make('brands')
                                    ->hiddenLabel()
                                    ->schema([
                                        Forms\Components\Select::make('brand_id')
                                            ->label('Marques')
                                            ->options(Brand::pluck('name', 'id'))
                                            ->multiple(true)
                                            ->searchable(),
                                        Forms\Components\Select::make('category_id')
                                            ->label('Catégorie')
                                            ->options(Category::pluck('name', 'id'))
                                            ->searchable(),
                                        Forms\Components\TextInput::make('discount')
                                            ->label('Remise')
                                            ->numeric(),
                                        Forms\Components\Toggle::make('is_excluded')
                                            ->label('Exclue')
                                            ->required(),
                                    ])
                                    ->columns(4),
                            ]),
                    ])->columnSpan(['lg' => 2]),
                    Forms\Components\Group::make([
                        Forms\Components\Section::make('Compte')->schema([
                            Forms\Components\ToggleButtons::make('role')
                                ->label('Type de compte')
                                ->options(UserRoleEnum::class)
                                ->inline()
                                ->required(),
                            Forms\Components\Toggle::make('is_actif')
                                ->label('Compte actif')
                                ->required(),
                            Forms\Components\Toggle::make('is_blocked')
                                ->label('Compte bloqué')
                                ->required(),
                            Forms\Components\Toggle::make('receive_cart_notification')
                                ->label('Notifications de panier')
                                ->required(),
                        ]),
                        Forms\Components\Section::make('En bref')
                            ->schema([
                                Forms\Components\Placeholder::make('summary_orders')
                                    ->label('Commandes')
                                    ->content(fn (User $record): string => (string) $record->orders()->count()),
                                Forms\Components\Placeholder::make('summary_logged_at')
                                    ->label('Dernière connexion')
                                    ->content(fn (User $record): string => $record->logged_at ? \Illuminate\Support\Carbon::parse($record->logged_at)->format('d/m/Y') : 'Jamais'),
                                Forms\Components\Placeholder::make('summary_created_at')
                                    ->label('Client depuis')
                                    ->content(fn (User $record): string => $record->created_at?->format('d/m/Y') ?? '—'),
                            ])
                            ->columns(2)
                            ->visible(fn (?User $record): bool => null !== $record),
                        Forms\Components\Section::make('Remarques')->schema([
                            Forms\Components\Textarea::make('divers')
                                ->hiddenLabel()
                                ->rows(4),
                        ]),
                    ])->columnSpan(['lg' => 1]),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            LocationsRelationManager::class,
            BrandsRelationManager::class,
            DisablesRelationManager::class,
            DiscountsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'customer-stats' => Pages\CustomerStats::route('/{record}/stats'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
}
