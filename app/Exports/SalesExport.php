<?php

declare(strict_types=1);

namespace App\Exports;

use App\DataObjects\Stats\SalesFilters;
use App\Enum\SalesGroupByEnum;
use App\Services\SalesStatsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

final class SalesExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(
        private readonly SalesFilters $filters,
        private readonly SalesGroupByEnum $groupBy,
    ) {}

    /**
     * @return Collection<int, non-empty-array<string, mixed>>
     */
    public function collection(): Collection
    {
        $isItem = SalesGroupByEnum::ITEM === $this->groupBy;

        return app(SalesStatsService::class)
            ->breakdownQuery($this->filters, $this->groupBy)
            ->orderByDesc('revenue')
            ->toBase()
            ->get()
            ->map(fn(object $row): array => array_merge(
                [$this->labelHeading() => $row->label],
                $isItem ? [
                    'Marque' => $row->brand_name,
                    'Catégorie' => $row->category_name,
                ] : [],
                [
                    'Quantité' => (int) $row->quantity,
                    'CA HT (€)' => round(((int) $row->revenue) / 100, 2),
                    'Commandes' => (int) $row->orders_count,
                    'Clients' => (int) $row->customers_count,
                    'Dernière vente' => Carbon::parse($row->last_sold_at)->format('d/m/Y'),
                ],
            ));
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return array_merge(
            [$this->labelHeading()],
            SalesGroupByEnum::ITEM === $this->groupBy ? ['Marque', 'Catégorie'] : [],
            ['Quantité', 'CA HT (€)', 'Commandes', 'Clients', 'Dernière vente'],
        );
    }

    private function labelHeading(): string
    {
        return SalesGroupByEnum::ITEM === $this->groupBy ? 'Référence' : $this->groupBy->label();
    }
}
