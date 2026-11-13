<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Actions\Item\LogItemPublishChangeAction;
use App\Enum\UserRoleEnum;
use App\Filament\Resources\ItemResource\Pages\CreateItem;
use App\Filament\Resources\ItemResource\Pages\EditItem;
use App\Filament\Resources\ItemResource\Pages\ListItems;
use App\Filament\Resources\ItemResource\RelationManagers\MetasRelationManager;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

final class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Produits';

    protected static ?string $modelLabel = 'produit';

    protected static ?string $pluralModelLabel = 'produits';

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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(3)->schema([
                Tabs::make('Fiche')
                    ->tabs([
                        Tab::make('Général')
                            ->schema([
                                Select::make('brand_id')
                                    ->label('Marque')
                                    ->options(Brand::pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),
                                Select::make('category_id')
                                    ->label('Catégorie')
                                    ->options(Category::pluck('name', 'id'))
                                    ->searchable(),
                                TextInput::make('reference')
                                    ->label('Référence')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('master_id')
                                    ->label('Produit maître')
                                    ->options(Item::pluck('reference', 'id'))
                                    ->searchable(),
                                TextInput::make('multiple_quantity')
                                    ->label('Quantité multiple')
                                    ->required()
                                    ->numeric()
                                    ->default(1),
                                TextInput::make('catalog_group')
                                    ->label('Groupe catalogue'),
                                Textarea::make('description')
                                    ->label('Description')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ])
                            ->columns(3),
                        Tab::make('Prix')
                            ->schema([
                                TextInput::make('price')
                                    ->label('Prix')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('€'),
                                TextInput::make('price_b2b')
                                    ->label('Prix B2B')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('€'),
                                TextInput::make('price_promo')
                                    ->label('Prix promo')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('€'),
                                TextInput::make('price_special1')
                                    ->label('Prix spécial 1')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('€'),
                                TextInput::make('price_special2')
                                    ->label('Prix spécial 2')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('€'),
                                TextInput::make('price_special3')
                                    ->label('Prix spécial 3')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('€'),
                                TextInput::make('price_fix')
                                    ->label('Prix fixe')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('€'),
                                TextInput::make('sale_price')
                                    ->label('Prix de vente conseillé')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('€'),
                                TextInput::make('reseller_price')
                                    ->label('Prix revendeur')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('€'),
                            ])
                            ->columns(3),
                        Tab::make('Photos')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('pictures')
                                    ->label('Photos')
                                    ->disk('items')
                                    ->enableReordering()
                                    ->image()
                                    ->appendFiles()
                                    ->preserveFilenames()
                                    ->multiple(),
                            ]),
                        Tab::make('Ventes')
                            ->schema([
                                View::make('filament.resources.item-resource.sales-tab'),
                            ])
                            ->visible(fn (?Item $record): bool => null !== $record),
                    ])
                    ->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make('Publication')
                        ->schema([
                            Toggle::make('is_published')->label('En ligne')->required(),
                            Toggle::make('is_new')->label('Nouveauté')->required(),
                            Toggle::make('is_best_seller')->label('Best-seller'),
                        ]),
                    Section::make('En bref')
                        ->schema([
                            Placeholder::make('summary_price_b2b')
                                ->label('Prix B2B')
                                ->content(fn (Item $record): string => Number::currency((float) $record->price_b2b, 'EUR', 'fr')),
                            Placeholder::make('summary_sold')
                                ->label('Commandes')
                                ->content(fn (Item $record): string => (string) $record->orders()->whereHas('order')->count()),
                            Placeholder::make('summary_pictures')
                                ->label('Photos')
                                ->content(fn (Item $record): string => (string) $record->getMedia()->count()),
                            Placeholder::make('summary_updated')
                                ->label('Modifié le')
                                ->content(fn (Item $record): string => $record->updated_at?->format('d/m/Y') ?? '—'),
                        ])
                        ->columns(2)
                        ->visible(fn (?Item $record): bool => null !== $record),
                ])
                    ->columnSpan(['lg' => 1]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->with('media'))
            ->columns([
                SpatieMediaLibraryImageColumn::make('pictures')
                    ->label('')
                    ->conversion('thumb')
                    ->limit(1)
                    ->square()
                    ->size(32),
                TextColumn::make('reference')
                    ->label('Référence')
                    ->weight(FontWeight::SemiBold)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->label('Marque')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('master.reference')
                    ->label('Produit maître')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price_b2b')
                    ->label('Prix B2B')
                    ->money('EUR', locale: 'fr')
                    ->alignRight()
                    ->sortable(),
                TextColumn::make('price_promo')
                    ->label('Promo')
                    ->formatStateUsing(fn($state): string => $state > 0 ? Number::currency((float) $state, 'EUR', 'fr') : '—')
                    ->color(fn($state): string => $state > 0 ? 'danger' : 'gray')
                    ->alignRight()
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('Statut')
                    ->boolean()
                    ->tooltip(fn($state): string => $state ? 'En ligne' : 'Hors ligne')
                    ->sortable(),
                IconColumn::make('is_best_seller')
                    ->label('Best-seller')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('multiple_quantity')
                    ->label('Quantité multiple')
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('orders_count')
                    ->label('Commandes')
                    // Like the sales statistics: lines of non-deleted orders only.
                    ->counts(['orders' => fn (Builder $query): Builder => $query->whereHas('order')])
                    ->alignRight()
                    ->sortable(),
                TextColumn::make('media_count')
                    ->label('Photos')
                    ->counts('media')
                    ->alignRight()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ])
            ->filters([
                SelectFilter::make('brand')->relationship('brand', 'name'),
                SelectFilter::make('category')->relationship(
                    'category',
                    'name',
                ),
                TernaryFilter::make('is_published'),
                TernaryFilter::make('is_new'),
                TernaryFilter::make('is_best_seller'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->actions([
                EditAction::make(),
                RestoreAction::make(),
                Action::make('Duplicate')
                    ->label('Dupliquer')
                    ->action(function ($record): void {
                        $new_item = $record->replicate();
                        $new_item->reference = $new_item->reference . '_copy';
                        $new_item->save();
                        $record->metas->each(
                            fn($metas) => $new_item->metas()->create($metas->toArray()),
                        );
                    })
                    ->requiresConfirmation()
                    ->tooltip('Dupliquer')
                    ->icon('heroicon-s-document-duplicate')
                    ->color('primary'),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                RestoreBulkAction::make(),
                BulkAction::make('published')
                    ->label('Mettre en ligne')
                    ->icon('heroicon-s-check-circle')
                    ->action(function (Collection $records): void {
                        $logAction = app(LogItemPublishChangeAction::class);
                        foreach ($records as $record) {
                            $oldValue = (bool) $record->is_published;
                            $record->is_published = true;
                            $record->save();

                            $logAction->handle(
                                item: $record,
                                oldValue: $oldValue,
                                newValue: true,
                                action: 'bulk_action',
                                userId: auth()->id(),
                                reason: 'Publication en masse'
                            );
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('unpublished')
                    ->label('Retirer du site')
                    ->icon('heroicon-s-x-mark')
                    ->action(function (Collection $records): void {
                        $logAction = app(LogItemPublishChangeAction::class);
                        foreach ($records as $record) {
                            $oldValue = (bool) $record->is_published;
                            $record->is_published = false;
                            $record->save();

                            $logAction->handle(
                                item: $record,
                                oldValue: $oldValue,
                                newValue: false,
                                action: 'bulk_action',
                                userId: auth()->id(),
                                reason: 'Dépublication en masse'
                            );
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getRelations(): array
    {
        return [MetasRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItems::route('/'),
            'create' => CreateItem::route('/create'),
            'edit' => EditItem::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) self::getModel()::count();
    }
}
