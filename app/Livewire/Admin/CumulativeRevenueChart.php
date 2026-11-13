<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\DataObjects\Stats\DashboardPeriod;
use App\Enum\UserRoleEnum;
use App\Services\SalesStatsService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Dashboard: revenue cumulated day by day, this month vs the previous one.
 *
 * Lives outside App\Filament\Widgets on purpose (not a discovered widget),
 * like SalesChart. The dashboard re-mounts it (wire:key) per month.
 */
final class CumulativeRevenueChart extends ChartWidget
{
    public ?string $month = null;

    protected static ?string $heading = 'CA HT cumulé, jour par jour';

    protected static ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected static ?string $maxHeight = '260px';

    protected int|string|array $columnSpan = 'full';

    public function mount(): void
    {
        abort_unless(UserRoleEnum::ADMIN === auth()->user()?->role, 403);

        parent::mount();
    }

    protected function getData(): array
    {
        $period = DashboardPeriod::for($this->month, Carbon::now());
        $stats = app(SalesStatsService::class);

        $current = $stats->dailyCumulative($period->filters());
        // Compare over the whole previous month so the curve shows where it ended.
        $previous = $stats->dailyCumulative(new \App\DataObjects\Stats\SalesFilters(
            from: $period->previousFrom,
            to: $period->previousFrom->copy()->endOfMonth()->startOfDay(),
        ));

        $days = max(count($previous), $period->from->daysInMonth);

        return [
            'datasets' => [
                [
                    'label' => $period->label(),
                    'data' => array_column($current, 'revenue'),
                    'borderColor' => '#111111',
                    'backgroundColor' => '#111111',
                    'borderWidth' => 2.5,
                    'pointRadius' => 0,
                    'tension' => 0.25,
                ],
                [
                    'label' => Carbon::parse($period->previousFrom)->locale('fr')->translatedFormat('F Y'),
                    'data' => array_column($previous, 'revenue'),
                    'borderColor' => '#cfc4b6',
                    'backgroundColor' => '#cfc4b6',
                    'borderDash' => [5, 4],
                    'borderWidth' => 2,
                    'pointRadius' => 0,
                    'tension' => 0.25,
                ],
            ],
            'labels' => range(1, $days),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): ?array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => [
                'y' => ['beginAtZero' => true, 'title' => ['display' => true, 'text' => 'CA HT (€)']],
            ],
        ];
    }
}
