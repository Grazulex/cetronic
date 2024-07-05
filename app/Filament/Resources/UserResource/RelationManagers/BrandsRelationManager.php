<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enum\PriceTypeEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\ItemMeta;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

final class BrandsRelationManager extends RelationManager
{
    protected static string $relationship = 'brands';

    protected static ?string $recordTitleAttribute = 'name';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Brand & category')
                    ->schema([
                        Forms\Components\Select::make('brand_id')
                            ->label('Brand')
                            ->options(Brand::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->options(Category::pluck('name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('category_meta_id', null)),
                        Forms\Components\Select::make('category_meta_id')
                            ->label('Category Meta')
                            ->options(function (callable $get) {
                                $category_id = $get('category_id');
                                if ($category_id) {
                                    return CategoryMeta::where('category_id', $category_id)->pluck('name', 'id');
                                }

                                return [];
                            })
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('category_meta_value', null)),
                        Forms\Components\Select::make('category_meta_value')
                            ->label('Category Meta Value')
                            ->options(function (callable $get) {
                                $category_meta_id = $get('category_meta_id');
                                if ($category_meta_id) {
                                    return ItemMeta::where('meta_id', $category_meta_id)->pluck('value', 'value');
                                }

                                return [];
                            })
                            ->searchable(),
                    ]),
                Forms\Components\Section::make('Price')
                    ->schema([
                        Forms\Components\TextInput::make('reduction')
                            ->numeric()
                            ->suffix(label: '%'),
                        Forms\Components\TextInput::make('coefficient')
                            ->numeric(),
                        Forms\Components\TextInput::make('addition_price')
                            ->numeric()
                            ->suffix('€'),
                        Forms\Components\Radio::make('price_type')
                            ->options(PriceTypeEnum::class)
                            ->default(PriceTypeEnum::DEFAULT->value)
                            ->required(),
                    ])->columns(2),
                Forms\Components\Section::make('Activations')
                    ->schema([
                        Forms\Components\Toggle::make('not_show_promo')
                            ->required(),
                        Forms\Components\Toggle::make('is_excluded')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('brand.name')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->searchable(),
                Tables\Columns\TextColumn::make('category_meta.name'),
                Tables\Columns\TextColumn::make('category_meta_value'),
                Tables\Columns\TextColumn::make('reduction')->suffix('%')->prefix('-'),
                Tables\Columns\TextColumn::make('coefficient')->prefix('x'),
                Tables\Columns\TextColumn::make('addition_price')->suffix('€')->prefix('+'),
                Tables\Columns\TextColumn::make('price_type'),
                Tables\Columns\IconColumn::make('not_show_promo')->boolean(),
                Tables\Columns\IconColumn::make('is_excluded')->boolean(),
            ])
            ->filters([

            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
