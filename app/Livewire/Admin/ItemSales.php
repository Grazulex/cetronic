<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\DataObjects\Stats\SalesFilters;
use App\Enum\UserRoleEnum;
use App\Services\SalesStatsService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * "Ventes" tab of the product edit form. Its own component, so the period
 * selector never ends up in the product form data.
 */
final class ItemSales extends Component
{
    public int $itemId;

    public string $period = 'this_year';

    public function mount(int $itemId): void
    {
        abort_unless(UserRoleEnum::ADMIN === auth()->user()?->role, 403);

        $this->itemId = $itemId;
    }

    public function render(SalesStatsService $service): View
    {
        [$from, $to] = SalesFilters::periodRange($this->period);
        $state = ['item_id' => $this->itemId, 'from' => $from, 'to' => $to];
        $filters = SalesFilters::fromArray($state);

        return view('livewire.admin.item-sales', [
            'periods' => SalesFilters::periods(),
            'summary' => $service->summary($filters),
            'topCustomers' => $service->topCustomers($filters, 10),
            'chartFilters' => $state,
        ]);
    }
}
