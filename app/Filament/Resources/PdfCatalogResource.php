<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PdfCatalogResource\Pages\PdfCatalogCreate;
use App\Filament\Resources\PdfCatalogResource\Pages\PdfCatalogList;
use App\Filament\Resources\PdfCatalogResource\Pages\PdfCatalogRegenerate;
use App\Filament\Resources\PdfCatalogResource\RelationManagers\TranslationsRelationManager;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ItemMeta;
use App\Models\PdfCatalog;
use Exception;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Support\Facades\Storage;

final class PdfCatalogResource extends Resource
{
    protected static ?string $model = PdfCatalog::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $navigationGroup = 'PDF CATALOG';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Configurations')->schema([
                Select::make(name: PdfCatalog::CONDITION_BRAND)
                    ->options(Brand::pluck(column: 'name', key: 'id'))
                    ->searchable()
                    ->multiple(),
                Select::make(name: PdfCatalog::CONDITION_CATEGORY)
                    ->options(Category::pluck(column: 'name', key: 'id'))
                    ->multiple()
                    ->searchable(),
                Select::make(name: PdfCatalog::CONDITION_GENDER)
                    ->options(ItemMeta::whereHas('meta', function ($q): void {
                        $q->where('name', PdfCatalog::META_GENDER);
                    })->select('value')->groupBy('value')->pluck(column: 'value', key: 'value'))
                    ->multiple()
                    ->searchable(),
                Select::make(name: PdfCatalog::CONDITION_TYPE)
                    ->options(ItemMeta::whereHas('meta', function ($q): void {
                        $q->where('name', PdfCatalog::META_TYPE);
                    })->select('value')->groupBy('value')->pluck(column: 'value', key: 'value'))
                    ->multiple()
                    ->searchable(),
            ]),
        ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')->sortable(),

                ViewColumn::make('condition_brands')
                    ->view('filament.tables.columns.condition_array_to_string'),
                ViewColumn::make('condition_categories')
                    ->view('filament.tables.columns.condition_array_to_string'),
                ViewColumn::make('condition_types')
                    ->view('filament.tables.columns.condition_array_to_string'),
                ViewColumn::make('condition_genders')
                    ->view('filament.tables.columns.condition_array_to_string'),
                TextColumn::make('created_at'),
                TextColumn::make('generated_at'),
            ])
            ->filters([

            ])
            ->actions([
                Action::make('regenerate')
                    ->action(fn(PdfCatalog $record) => $record->regenerate()),
                Action::make('download')
                    ->disabled(fn(PdfCatalog $record): bool => ! (bool) $record->url)
                    ->url(fn(PdfCatalog $record): string => Storage::url($record->url))
                    ->openUrlInNewTab(),
                DeleteAction::make(),
            ])
            ->bulkActions([DeleteBulkAction::make()])
            ->defaultSort('created_at', 'desc');

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
            'index' => PdfCatalogList::route('/'),
            'create' => PdfCatalogCreate::route('/create'),
            'regenerate' => PdfCatalogRegenerate::route('/regenerate'),
        ];
    }

    protected static function getNavigationBadge(): ?string
    {
        return (string) self::getModel()::count();
    }
}
