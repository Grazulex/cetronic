<?php

declare(strict_types=1);

namespace App\Filament\Resources\CartResource\RelationManagers;

use App\Models\Item;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

final class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(schema: [
                Forms\Components\Select::make('item_id')
                    ->options(Item::pluck('reference', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->suffix('€'),
                Forms\Components\TextInput::make('price_promo')
                    ->numeric()
                    ->suffix('€'),
                Forms\Components\TextInput::make('quantity')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item.brand.name'),
                Tables\Columns\TextColumn::make('item.reference'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('price_old')->label('Base price'),
                Tables\Columns\TextColumn::make('price_promo')->label('Price promo'),
                Tables\Columns\TextColumn::make('price')->label('Price after discount'),
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
