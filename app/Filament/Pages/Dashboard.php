<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\DataObjects\Stats\DashboardPeriod;
use App\Enum\SalesGroupByEnum;
use App\Enum\UserRoleEnum;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\UserResource;
use App\Models\Order;
use App\Models\User;
use App\Services\SalesStatsService;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;

/**
 * Admin home: the sales of the month, compared at the same date with the
 * previous month. Admins only see figures; agents get a welcome text.
 */
final class Dashboard extends BaseDashboard
{
    #[Url(as: 'mois')]
    public ?string $month = null;

    protected static string $view = 'filament.pages.dashboard';

    public function getHeading(): string
    {
        return $this->isAdmin() ? $this->period()->label() : 'Bienvenue';
    }

    public function getSubheading(): ?string
    {
        return $this->isAdmin() ? $this->period()->rangeLabel() : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        if ( ! $this->isAdmin()) {
            return ['isAdmin' => false];
        }

        $period = $this->period();
        $stats = app(SalesStatsService::class);

        $current = $stats->summary($period->filters());
        $previous = $stats->summary($period->previousFilters());
        $lastYear = $stats->summary($period->lastYearFilters());

        $orderDates = [$period->from->copy()->startOfDay(), $period->to->copy()->endOfDay()];

        return [
            'isAdmin' => true,
            'period' => $period,
            'current' => $current,
            'changes' => [
                'revenue' => $this->change($current['revenue'], $previous['revenue']),
                'revenue_last_year' => $this->change($current['revenue'], $lastYear['revenue']),
                'orders' => $this->change($current['orders'], $previous['orders']),
                'average_order' => $this->change($current['average_order'], $previous['average_order']),
                'customers' => $this->change($current['customers'], $previous['customers']),
            ],
            'lastYearLabel' => $period->lastYearFrom->copy()->locale('fr')->translatedFormat('M Y'),
            'newCustomers' => $stats->newCustomers($period->filters()),
            'toActivate' => User::query()->where('role', UserRoleEnum::CUSTOMER->value)->where('is_actif', false)->count(),
            'toActivateUrl' => UserResource::getUrl('index', ['activeTab' => 'a-activer']),
            'topBrands' => $stats->breakdownQuery($period->filters(), SalesGroupByEnum::BRAND)
                ->orderByDesc('revenue')->limit(5)->get(),
            'topProducts' => $stats->breakdownQuery($period->filters(), SalesGroupByEnum::ITEM)
                ->orderByDesc('quantity')->limit(5)->get(),
            'latestOrders' => Order::query()
                ->with('user')
                ->whereBetween('created_at', $orderDates)
                ->latest()
                ->limit(5)
                ->get(),
            'orderUrl' => fn(Order $order): string => OrderResource::getUrl('edit', ['record' => $order]),
        ];
    }

    private function period(): DashboardPeriod
    {
        return DashboardPeriod::for($this->month, Carbon::now());
    }

    private function isAdmin(): bool
    {
        return UserRoleEnum::ADMIN === auth()->user()?->role;
    }

    /**
     * @return ?array{up: bool, percent: int} null when there is nothing to compare with
     */
    private function change(float|int $current, float|int $previous): ?array
    {
        if (0.0 === (float) $previous) {
            return null;
        }

        $ratio = ($current - $previous) / $previous;

        return ['up' => $ratio >= 0, 'percent' => (int) round(abs($ratio) * 100)];
    }
}
