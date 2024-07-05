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
use App\Models\User;
use App\Services\UserService;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Users';

    public static function canViewAny(): bool
    {
        return UserRoleEnum::ADMIN === auth()->user()?->role;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('external_reference')
                            ->prefix('#: ')
                            ->searchable()
                            ->sortable(),
                    ]),
                    Stack::make([
                        TextColumn::make('name')
                            ->searchable()
                            ->sortable(),
                        TextColumn::make('email')
                            ->searchable()
                            ->sortable(),
                        TextColumn::make('role')
                            ->searchable()
                            ->sortable(),
                    ]),
                    Stack::make([
                        IconColumn::make('is_actif')
                            ->boolean(),
                    ]),
                    Stack::make([
                        BadgeColumn::make('orders_count')
                            ->counts(
                                'orders',
                            )
                            ->suffix(' orders')
                            ->color('primary')
                            ->sortable(),
                    ]),
                    Stack::make([
                        TextColumn::make('created_at')
                            ->prefix('created: ')
                            ->date('d/m/Y')
                            ->sortable()
                            ->searchable(),
                        TextColumn::make('updated_at')
                            ->prefix('updated: ')
                            ->date('d/m/Y')
                            ->sortable()
                            ->searchable(),
                        TextColumn::make('logged_at')
                            ->prefix('logged: ')
                            ->date('d/m/Y')
                            ->sortable()
                            ->searchable(),
                    ]),
                ]),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions(actions: [
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Login')
                    ->label(__('Login with this user'))
                    ->action(function ($record) {
                        $user = User::find($record->id);
                        auth()->login($user, $remember = true);
                        return redirect()->route('home');
                    })
                    ->icon('heroicon-s-key')
                    ->color('primary'),
                Tables\Actions\Action::make('Print')
                    ->label(__('PDF'))
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
                Forms\Components\Section::make('Main fields')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('external_reference')
                            ->maxLength(255),
                        Forms\Components\Radio::make('role')
                            ->options(UserRoleEnum::class)
                            ->required(),
                        Forms\Components\Select::make('language')
                            ->options(['fr' => 'Français', 'en' => 'English', 'nl' => 'Nederlands'])
                            ->searchable(),
                    ])->columns(2),
                Forms\Components\Section::make('Shipping')->schema([
                    Forms\Components\TextInput::make('franco')
                        ->helperText('Minimum order total price that set delivery price to free')
                        ->numeric()
                        ->suffix('€'),
                    Forms\Components\TextInput::make('shipping_price')
                        ->helperText('Fixed shipping price for each order')
                        ->numeric()
                        ->suffix('€'),
                ])->columns(2),
                Forms\Components\Section::make('Activations')->schema([
                    Forms\Components\Select::make('agent_id')
                        ->options(User::pluck('name', 'id'))
                        ->searchable(),
                    Forms\Components\Toggle::make('is_actif')
                        ->required(),
                    Forms\Components\Toggle::make('is_blocked')
                        ->required(),
                    Forms\Components\Toggle::make('receive_cart_notification')
                        ->required(),
                    Forms\Components\Textarea::make('divers'),
                ])->columns(2),
                Forms\Components\Section::make('Shortcut creation')->schema([
                    Forms\Components\Repeater::make('brands')
                        ->schema([
                            Forms\Components\Select::make('brand_id')
                                ->options(Brand::pluck('name', 'id'))
                                ->multiple(true)
                                ->searchable(),
                            Forms\Components\Select::make('category_id')
                                ->options(Category::pluck('name', 'id'))
                                ->searchable(),
                            Forms\Components\TextInput::make('discount')
                                ->numeric(),
                            Forms\Components\Toggle::make('is_excluded')
                                ->required(),
                        ])
                        ->columns(4),
                ])->columns(1),
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
        ];
    }

    protected static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
}
