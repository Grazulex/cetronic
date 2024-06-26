<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enum\UserRoleEnum;
use App\Exports\CategoriesExport;
use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Filament\Resources\CategoryResource\RelationManagers\MetasRelationManager;
use App\Filament\Resources\CategoryResource\RelationManagers\TranslationsRelationManager;
use App\Models\Category;
use Carbon\Carbon;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

final class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive';

    protected static ?string $navigationGroup = 'Items';

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === UserRoleEnum::ADMIN;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Main fields')
                ->schema([
                    TextInput::make('name')->required(),
                    FileUpload::make('picture')
                        ->directory('categories')
                        ->image(),
                ])
                ->columns(2),
            Section::make('Activation')
                ->schema(components: [
                    Toggle::make('is_published')->required(),
                    Toggle::make('is_featured')->required(),
                    Toggle::make('is_export')->required(),
                    Toggle::make('show_picture_variant')->required(),
                    TextInput::make('order')
                        ->numeric()
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('picture'),
                TextColumn::make('name')->searchable(),
                IconColumn::make('is_published')->boolean(),
                IconColumn::make('is_featured')->boolean(),
                IconColumn::make('is_export')->boolean(),
                TextColumn::make('order'),
                BadgeColumn::make('metas_count')->counts(
                    'metas'
                ),
                BadgeColumn::make('items_count')->counts(
                    'items'
                ),
            ])
            ->filters([

            ])
            ->actions([
                EditAction::make(),
                Action::make('exportCSV')
                    ->label(__('Export XLSX'))
                    ->action(fn ($record) => (new CategoriesExport($record))->download($record->name.'_'.Carbon::now().'.xlsx'))
                    ->tooltip(__('Export'))
                    ->icon('heroicon-s-download')
                    ->color('primary'),
            ])
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function getRelations(): array
    {
        return [
            MetasRelationManager::class,
            TranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }

    protected static function getNavigationBadge(): ?string
    {
        return (string)static::getModel()::count();
    }
}
