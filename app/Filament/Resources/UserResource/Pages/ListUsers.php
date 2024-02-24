<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Exports\UsersExport;
use App\Filament\Resources\UserResource;
use Carbon\Carbon;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function exportXLSX(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new UsersExport(), 'Users_'.Carbon::now().'.xlsx');
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('export')
                ->label('Export to XLSX')
                ->action('exportXLSX'),
        ];
    }
}
