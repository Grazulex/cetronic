<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

final class MetasRelationManager extends RelationManager
{
    protected static string $relationship = 'metas';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Toggle::make('is_color')->label('Show like a color')->helperText('If checked, the value will be shown as a color and you need to insert RGB color code'),
                Forms\Components\Toggle::make('is_meta')->label('Show in filter (sidebare)'),
                Forms\Components\Toggle::make('is_export')->label('Need to be exported for agent'),
                Forms\Components\Toggle::make('is_variant')->label('Show in variants table'),
                Forms\Components\Toggle::make('is_choice')->label('Show as a choice in item form'),
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\IconColumn::make('is_color')->boolean(),
                Tables\Columns\IconColumn::make('is_export')->boolean(),
                Tables\Columns\IconColumn::make('is_meta')->boolean(),
                Tables\Columns\IconColumn::make('is_variant')->boolean(),
                Tables\Columns\IconColumn::make('is_choice')->boolean(),
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
