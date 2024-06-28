<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enum\UserRoleEnum;
use App\Filament\Resources\ItemResource\Pages\CreateItem;
use App\Filament\Resources\ItemResource\Pages\EditItem;
use App\Filament\Resources\ItemResource\Pages\ListItems;
use App\Filament\Resources\ItemResource\RelationManagers\MetasRelationManager;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Support\Collection;

final class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationGroup = 'Items';

    public static function canViewAny(): bool
    {
        return UserRoleEnum::ADMIN === auth()->user()?->role;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Heading')
                ->tabs([
                    Tab::make('Brand & category')
                        ->schema([
                            Select::make('brand_id')
                                ->options(Brand::pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                            Select::make('category_id')
                                ->options(Category::pluck('name', 'id'))
                                ->searchable(),
                        ]),
                    Tab::make('Master data')
                        ->schema([
                            TextInput::make('reference')
                                ->required()
                                ->maxLength(255),
                            Select::make('master_id')
                                ->options(Item::pluck('reference', 'id'))
                                ->searchable(),
                            TextInput::make('multiple_quantity')
                                ->required()
                                ->numeric()
                                ->default(1),
                            Textarea::make('description')
                                ->columns(2),
                        ])
                        ->columns(2),
                    Tab::make('Activations')
                        ->schema([
                            Toggle::make('is_new')->required(),
                            Toggle::make('is_published')->required(),
                        ])
                        ->columns(2),
                    Tab::make('Pictures')
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('pictures')
                                ->disk('items')
                                ->enableReordering()
                                ->image()
                                ->appendFiles()
                                ->preserveFilenames()
                                ->multiple(),
                        ]),
                    Tab::make('Prices')
                        ->schema([
                            TextInput::make('price')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->suffix('€'),
                            TextInput::make('price_b2b')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->suffix('€'),
                            TextInput::make('price_promo')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->suffix('€'),
                            TextInput::make('price_special1')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->suffix('€'),
                            TextInput::make('price_special2')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->suffix('€'),
                            TextInput::make('price_special3')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->suffix('€'),
                            TextInput::make('price_fix')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->suffix('€'),
                            TextInput::make('sale_price')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->suffix('€'),
                        ])
                        ->columns(4),
                ])->columnSpan('full'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('brand.name')
                            ->searchable()
                            ->sortable(),
                        TextColumn::make('category.name')
                            ->searchable()
                            ->sortable(),
                    ]),
                    Stack::make([
                        TextColumn::make('reference')
                            ->searchable()
                            ->sortable(),
                        TextColumn::make('master.reference')
                            ->prefix('master: ')
                            ->sortable(),
                    ]),
                    Stack::make([
                        IconColumn::make('is_published')->boolean(),
                    ]),
                    Stack::make([
                        TextColumn::make('price_b2b')->prefix('b2b: ')->suffix('€')->alignRight(),
                        TextColumn::make('price_promo')->prefix('promo: ')->suffix('€')->alignRight(),
                        TextColumn::make('multiple_quantity')->prefix('multi: ')->alignRight(),
                    ]),
                    Stack::make([
                        BadgeColumn::make('orders_count')
                            ->counts(
                                'orders',
                            )
                            ->suffix(' orders')
                            ->color('secondary')
                            ->sortable(),
                        BadgeColumn::make('media_count')
                            ->counts(
                                'media',
                            )
                            ->suffix(' pictures')
                            ->color('primary')
                            ->sortable(),
                    ]),
                    Stack::make([
                        TextColumn::make('created_at')
                            ->prefix('created: ')
                            ->date('d/m/Y')
                            ->sortable(),
                        TextColumn::make('updated_at')
                            ->prefix('updated: ')
                            ->date('d/m/Y')
                            ->sortable(),
                    ]),
                ]),
            ])
            ->filters([
                SelectFilter::make('brand')->relationship('brand', 'name'),
                SelectFilter::make('category')->relationship(
                    'category',
                    'name',
                ),
                TernaryFilter::make('is_published'),
                TernaryFilter::make('is_new'),
            ])
            ->actions([
                EditAction::make(),
                Action::make('Duplicate')
                    ->label(__('Duplicate'))
                    ->action(function ($record): void {
                        $new_item = $record->replicate();
                        $new_item->reference = $new_item->reference . '_copy';
                        $new_item->save();
                        $record->metas->each(
                            fn($metas) => $new_item->metas()->create($metas->toArray()),
                        );
                    })
                    ->requiresConfirmation()
                    ->tooltip(__('Duplicate'))
                    ->icon('heroicon-s-duplicate')
                    ->color('primary'),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                BulkAction::make('published')
                    ->label(__('Published selection'))
                    ->icon('heroicon-s-check-circle')
                    ->action(function (Collection $records): void {
                        foreach ($records as $record) {
                            $record->is_published = true;
                            $record->save();
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('unpublished')
                    ->label(__('Unpublished selection'))
                    ->icon('heroicon-s-x')
                    ->action(function (Collection $records): void {
                        foreach ($records as $record) {
                            $record->is_published = false;
                            $record->save();
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

    protected static function getNavigationBadge(): ?string
    {
        return (string) self::getModel()::count();
    }
}
