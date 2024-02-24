<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryMetaResource\RelationManagers\TranslationsRelationManager;
use App\Models\CategoryMeta;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\CategoryMetaResource\Pages\ListCategoryMetas;
use App\Filament\Resources\CategoryMetaResource\Pages\EditCategoryMeta;

final class CategoryMetaResource extends Resource
{
    protected static ?string $model = CategoryMeta::class;

    protected static ?string $navigationIcon = 'heroicon-o-translate';

    protected static ?string $label = 'Metas translation';

    protected static ?string $navigationGroup = 'Items';

    protected static ?int $navigationSort = 99;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required()->disabled(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')->searchable(),
                TextColumn::make('name')->searchable(),
                BadgeColumn::make('translations_count')->counts(
                    'translations'
                ),
            ])
            ->filters([

            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            TranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategoryMetas::route('/'),
            'edit' => EditCategoryMeta::route('/{record}/edit'),
        ];
    }
}
