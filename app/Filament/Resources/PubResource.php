<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enum\UserRoleEnum;
use App\Filament\Resources\PubResource\Pages;
use App\Models\Pub;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class PubResource extends Resource
{
    protected static ?string $model = Pub::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationGroup = 'Items';

    public static function canViewAny(): bool
    {
        return UserRoleEnum::ADMIN === auth()->user()?->role;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Main fields')->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('title')
                    ->maxLength(255),
                TextInput::make('url')
                    ->url()
                    ->maxLength(255),
                Select::make('type')
                    ->options([
                        'pub_1' => 'Pub 1',
                        'pub_2' => 'Pub 2',
                        'pub_3' => 'Pub 3',
                        'pub_4' => 'Pub 4',
                        'pub_5' => 'Pub 5',
                    ])
                    ->required(),
                Select::make('language')
                    ->options([
                        'en' => 'English',
                        'nl' => 'Dutch',
                        'fr' => 'French',
                    ])
                    ->required(),
            ]),
            Section::make('Pictures')->schema([
                FileUpload::make('picture')
                    ->directory('pubs')
                    ->required()
                    ->image(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('picture'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('language')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([

            ])
            ->actions([
            ])
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPubs::route('/'),
            'create' => Pages\CreatePub::route('/create'),
            'edit' => Pages\EditPub::route('/{record}/edit'),
        ];
    }
}
