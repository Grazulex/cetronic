<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enum\UserRoleEnum;
use App\Exports\CategoriesExport;
use App\Filament\Resources\BrandResource\Pages\CreateBrand;
use App\Filament\Resources\BrandResource\Pages\EditBrand;
use App\Filament\Resources\BrandResource\Pages\ListBrands;
use App\Filament\Resources\BrandResource\RelationManagers\TranslationsRelationManager;
use App\Models\Brand;
use App\Models\Category;
use Carbon\Carbon;
use Exception;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

final class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $navigationGroup = 'Items';

    public static function canViewAny(): bool
    {
        return UserRoleEnum::ADMIN === auth()->user()?->role;
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('picture'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_published')->boolean(),
                IconColumn::make('is_featured')->boolean(),
            ])
            ->filters([

            ])
            ->actions([
                EditAction::make(),
                Action::make('exportCSV')
                    ->label(__('Export XLXS'))
                    ->action(fn($record, array $data) => (new CategoriesExport(Category::find($data['category_id']), $record))->download($record->name . '_' . Carbon::now() . '.xlsx'))
                    ->form([
                        Select::make('category_id')
                            ->label('Category')
                            ->options(Category::query()->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->tooltip(__('Export'))
                    ->icon('heroicon-s-download')
                    ->color('primary'),
            ])
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Main fields')->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')->required(),
            ]),
            Section::make('Pictures')->schema([
                FileUpload::make('picture')
                    ->directory('brands')
                    ->image(),
            ]),
            Section::make('Activation')
                ->schema([
                    Toggle::make('is_published')->required(),
                    Toggle::make('is_featured')->required(),
                    Toggle::make('is_register')->required()->helperText('Is showing on the register form.'),
                    Toggle::make('is_upload_actif')->required()->helperText('Items are active when upload picture'),
                    TextInput::make('order_register')->numeric()->required(),
                ])
                ->columns(4),
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
            'index' => ListBrands::route('/'),
            'create' => CreateBrand::route('/create'),
            'edit' => EditBrand::route('/{record}/edit'),
        ];
    }

    protected static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
}
