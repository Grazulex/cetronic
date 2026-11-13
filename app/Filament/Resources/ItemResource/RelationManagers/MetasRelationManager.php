<?php

declare(strict_types=1);

namespace App\Filament\Resources\ItemResource\RelationManagers;

use App\Models\CategoryMeta;
use App\Models\ItemMeta;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

final class MetasRelationManager extends RelationManager
{
    protected static string $relationship = 'metas';

    protected static ?string $recordTitleAttribute = 'value';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('meta_id')
                    ->options(CategoryMeta::pluck('name', 'id'))
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn(Set $set) => $set('value', null))
                    ->required(),
                Select::make('value')
                    ->options(function (Get $get) {
                        $meta = CategoryMeta::where('id', $get('meta_id'))->first();
                        if ($meta) {
                            return ItemMeta::where('meta_id', $get('meta_id'))->orderBy('value')->pluck('value', 'value');
                        }
                        return ItemMeta::orderBy('value')->pluck('value', 'value');
                    })
                    ->searchable()
                    ->required()
                    ->visible(fn(Get $get) => false === CategoryMeta::where('id', $get('meta_id'))->pluck('is_color')->first()),
                TextInput::make('value')
                    ->visible(fn(Get $get) => false === CategoryMeta::where('id', $get('meta_id'))->pluck('is_color')->first()),
                ColorPicker::make('color')
                    ->required()
                    ->visible(fn(Get $get) => true === CategoryMeta::where('id', $get('meta_id'))->pluck('is_color')->first()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('meta.name')->searchable(),
                TextColumn::make('value')
                    ->searchable(),
                BadgeColumn::make('color')->extraAttributes(fn(Model $record): array => ['style' => 'background:' . $record->color]),
            ])
            ->filters([

            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
