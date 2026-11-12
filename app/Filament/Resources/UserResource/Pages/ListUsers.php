<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Exports\BrandDiscounts\BrandDiscountsExport;
use App\Exports\UsersExport;
use App\Filament\Resources\UserResource;
use App\Models\Brand;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function exportXLSX(): BinaryFileResponse
    {
        return Excel::download(new UsersExport(), 'Users_' . Carbon::now() . '.xlsx');
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('export')
                ->label('Export to XLSX')
                ->action('exportXLSX'),
            Action::make('exportBrandDiscounts')
                ->label('Export remises marque')
                ->icon('heroicon-o-download')
                ->form([
                    Select::make('brand_id')
                        ->label('Marque')
                        ->options(fn() => Brand::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): BinaryFileResponse {
                    $export = new BrandDiscountsExport(Brand::findOrFail($data['brand_id']));

                    return Excel::download($export, $export->fileName());
                }),
        ];
    }
}
