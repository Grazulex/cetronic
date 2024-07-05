<?php

declare(strict_types=1);

namespace App\Filament\Resources\ItemResource\RelationManagers;

use App\Models\CategoryMeta;
use App\Models\ItemMeta;
use Closure;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('meta_id')
                    ->options(CategoryMeta::pluck('name', 'id'))
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn(callable $set) => $set('value', null))
                    ->required(),
                Select::make('value')
                    ->options(function (callable $get) {
                        $meta = CategoryMeta::where('id', $get('meta_id'))->first();
                        if ($meta) {
                            return ItemMeta::where('meta_id', $get('meta_id'))->orderBy('value')->pluck('value', 'value');
                        }
                        return ItemMeta::orderBy('value')->pluck('value', 'value');
                    })
                    ->searchable()
                    ->required()
                    ->visible(fn(Closure $get) => false === CategoryMeta::where('id', $get('meta_id'))->pluck('is_color')->first()),
                TextInput::make('value')
                    ->visible(fn(Closure $get) => false === CategoryMeta::where('id', $get('meta_id'))->pluck('is_color')->first()),
                ColorPicker::make('color')
                    ->required()
                    ->visible(fn(Closure $get) => true === CategoryMeta::where('id', $get('meta_id'))->pluck('is_color')->first()),
            ]);
    }

    public static function table(Table $table): Table
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
