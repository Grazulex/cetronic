<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\DataObjects\Stats\SalesFilters;
use App\Enum\SalesGroupByEnum;
use App\Filament\Resources\UserResource;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\SalesStatsService;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\Page;

class CustomerStats extends Page
{
    public User $record;
    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.resources.user-resource.pages.customer-stats';

    protected static ?string $title = 'Statistiques Client';

    public function mount($record): void
    {
        $this->record = $record;

        // Vérifier que c'est bien un client
        if (\App\Enum\UserRoleEnum::CUSTOMER !== $this->record->role) {
            abort(403, 'Cette page est réservée aux clients.');
        }
    }

    protected function getActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour à la liste')
                ->url(UserResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    protected function getTitle(): string
    {
        return 'Statistiques de ' . $this->record->name;
    }

    protected function getViewData(): array
    {
        $service = app(SalesStatsService::class);
        $filters = new SalesFilters(userId: $this->record->id);
        [$chartFrom] = SalesFilters::periodRange('last_12_months');

        $orders = $this->record->orders()->get();
        $lines = OrderItem::query()
            ->whereIn('order_id', $orders->modelKeys())
            ->selectRaw('order_id, COUNT(*) as products, SUM(quantity) as quantity')
            ->groupBy('order_id')
            ->toBase()
            ->get()
            ->keyBy('order_id');

        return [
            'record' => $this->record,
            'summary' => $service->summary($filters),
            'lastOrder' => $orders->first(),
            'chartFilters' => ['user_id' => $this->record->id, 'from' => $chartFrom, 'to' => now()->toDateString()],
            'brandStats' => $service->breakdownQuery($filters, SalesGroupByEnum::BRAND)->orderByDesc('revenue')->toBase()->get(),
            'categoryStats' => $service->breakdownQuery($filters, SalesGroupByEnum::CATEGORY)->orderByDesc('revenue')->toBase()->get(),
            'orders' => $orders,
            'orderLines' => $lines,
        ];
    }
}
