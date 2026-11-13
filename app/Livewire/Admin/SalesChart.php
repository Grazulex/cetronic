<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\DataObjects\Stats\SalesFilters;
use App\Enum\UserRoleEnum;
use App\Services\SalesStatsService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Monthly revenue (bars) and quantity (line) for a set of sales filters.
 *
 * Lives outside App\Filament\Widgets on purpose: it must not be discovered
 * as a dashboard widget. Parents re-mount it (wire:key) when filters change.
 */
final class SalesChart extends ChartWidget
{
    /** @var array<string, mixed> */
    public array $filters = [];

    protected static ?string $heading = 'Évolution mensuelle';

    protected static ?string $pollingInterval = null;

    // Embedded in the stats pages: render the data with the page, not in a second request.
    protected static bool $isLazy = false;

    protected static ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = 'full';

    public function mount(): void
    {
        abort_unless(UserRoleEnum::ADMIN === auth()->user()?->role, 403);

        parent::mount();
    }

    protected function getData(): array
    {
        $months = app(SalesStatsService::class)->monthly(SalesFilters::fromArray($this->filters));

        return [
            'datasets' => [
                [
                    'label' => 'CA HT (€)',
                    'data' => array_column($months, 'revenue'),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.6)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'yAxisID' => 'y',
                    'order' => 2,
                ],
                [
                    'type' => 'line',
                    'label' => 'Quantité',
                    'data' => array_column($months, 'quantity'),
                    'backgroundColor' => 'rgb(245, 158, 11)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'yAxisID' => 'y1',
                    'order' => 1,
                ],
            ],
            'labels' => array_map(
                fn(array $month): string => Carbon::parse($month['month'] . '-01')->format('m/Y'),
                $months,
            ),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): ?array
    {
        return [
            'scales' => [
                'y' => ['type' => 'linear', 'position' => 'left', 'beginAtZero' => true, 'title' => ['display' => true, 'text' => 'CA HT (€)']],
                'y1' => ['type' => 'linear', 'position' => 'right', 'beginAtZero' => true, 'grid' => ['drawOnChartArea' => false], 'title' => ['display' => true, 'text' => 'Quantité']],
            ],
        ];
    }
}
