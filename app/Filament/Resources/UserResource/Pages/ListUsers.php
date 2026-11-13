<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Exports\BrandDiscounts\BrandDiscountsExport;
use App\Exports\UsersExport;
use App\Filament\Resources\UserResource;
use App\Models\Brand;
use Carbon\Carbon;
use Closure;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function exportXLSX(): BinaryFileResponse
    {
        return Excel::download(new UsersExport(), 'Users_' . Carbon::now() . '.xlsx');
    }

    /**
     * Quick filters shown as pills above the list.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $role = fn(string $role): Closure => fn(Builder $query): Builder => $query->where('role', $role);
        $waiting = fn(Builder $query): Builder => $query->where('role', 'customer')->where('is_actif', false);

        return [
            'tous' => Tab::make('Tous'),
            'clients' => Tab::make('Clients')->modifyQueryUsing($role('customer')),
            'agents' => Tab::make('Agents')->modifyQueryUsing($role('agent')),
            'administrateurs' => Tab::make('Administrateurs')->modifyQueryUsing($role('admin')),
            'a-activer' => Tab::make('À activer')
                ->modifyQueryUsing($waiting)
                ->badge(UserResource::getEloquentQuery()->withoutTrashed()->where('role', 'customer')->where('is_actif', false)->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('export')
                ->label('Exporter en Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action('exportXLSX'),
            Action::make('exportBrandDiscounts')
                ->label('Export remises marque')
                ->icon('heroicon-o-arrow-down-tray')
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
