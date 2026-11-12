<?php

declare(strict_types=1);

use App\Exports\SalesExport;
use App\Filament\Pages\SalesStats;
use App\Filament\Resources\ItemResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

use function Pest\Livewire\livewire;

use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    Carbon::setTestNow('2026-11-09 10:00:00');

    $this->brandA = Brand::factory()->create(['name' => 'Alpha']);
    $this->brandB = Brand::factory()->create(['name' => 'Beta']);
    $category = Category::factory()->create(['name' => 'Rings']);

    $this->itemA = Item::factory()->create(['reference' => 'REF-A', 'brand_id' => $this->brandA->id, 'category_id' => $category->id]);
    $this->itemB = Item::factory()->create(['reference' => 'REF-B', 'brand_id' => $this->brandB->id, 'category_id' => $category->id]);
});

afterEach(fn() => Carbon::setTestNow());

it('is reserved to admins', function (string $role, int $status): void {
    'admin' === $role ? loginAsAdmin() : ('agent' === $role ? loginAsAgent() : loginAsUser());

    $this->get(SalesStats::getUrl())->assertStatus($status);
})->with([
    'admin' => ['admin', Response::HTTP_OK],
    'agent' => ['agent', Response::HTTP_FORBIDDEN],
    'customer' => ['customer', Response::HTTP_FORBIDDEN],
]);

it('opens on the current year', function (): void {
    loginAsAdmin();
    createSale($this->itemA, 1, 10, '2026-02-01');
    createSale($this->itemB, 1, 10, '2025-12-31');

    livewire(SalesStats::class)
        ->assertSet('filters.from', fn(string $from): bool => str_starts_with($from, '2026-01-01'))
        ->assertSet('filters.to', fn(string $to): bool => str_starts_with($to, '2026-11-09'))
        ->assertSee('REF-A')
        ->assertDontSee('REF-B');
});

it('applies the brand filter to the table and the key figures', function (): void {
    loginAsAdmin();
    createSale($this->itemA, 1, 10, '2026-02-01');
    createSale($this->itemB, 2, 250, '2026-02-01');

    livewire(SalesStats::class)
        ->set('filters.brands', [$this->brandB->id])
        ->assertSee('REF-B')
        ->assertDontSee('REF-A')
        ->assertSee('500,00 €');
});

it('switches the period with the shortcuts', function (): void {
    loginAsAdmin();

    livewire(SalesStats::class)
        ->set('filters.period', 'last_year')
        ->assertSet('filters.from', '2025-01-01')
        ->assertSet('filters.to', '2025-12-31');
});

it('groups the table per brand', function (): void {
    loginAsAdmin();
    createSale($this->itemA, 1, 10, '2026-02-01');

    livewire(SalesStats::class)
        ->set('filters.group_by', 'brand')
        ->assertSee('Alpha')
        ->assertDontSee('REF-A');
});

it('shows the brand and category of each product', function (): void {
    loginAsAdmin();
    createSale($this->itemA, 1, 10, '2026-02-01');

    livewire(SalesStats::class)
        ->assertSeeInOrder(['REF-A', 'Alpha', 'Rings']);
});

it('links each product row to its edit page', function (): void {
    loginAsAdmin();
    createSale($this->itemA, 1, 10, '2026-02-01');

    livewire(SalesStats::class)
        ->assertSeeHtml(ItemResource::getUrl('edit', ['record' => $this->itemA->fresh()]));
});

it('refreshes the table headers as soon as the grouping changes', function (): void {
    loginAsAdmin();
    createSale($this->itemA, 1, 10, '2026-02-01');

    livewire(SalesStats::class)
        ->assertSeeHtml('fi-table-header-cell-brand-name')
        ->set('filters.group_by', 'brand')
        ->assertDontSeeHtml('fi-table-header-cell-brand-name')
        ->assertDontSeeHtml('fi-table-header-cell-category-name');
});

it('keeps the period shortcut when the date pickers echo its dates back', function (): void {
    loginAsAdmin();

    livewire(SalesStats::class)
        ->set('filters.period', 'last_year')
        ->set('filters.from', '2025-01-01 00:00:00')
        ->set('filters.to', '2025-12-31 00:00:00')
        ->assertSet('filters.period', 'last_year')
        ->set('filters.from', '2025-03-01 00:00:00')
        ->assertSet('filters.period', 'custom');
});

it('exports the filtered rows to Excel', function (): void {
    Excel::fake();
    loginAsAdmin();
    createSale($this->itemA, 1, 10, '2026-02-01');
    createSale($this->itemB, 3, 20, '2026-02-01');

    livewire(SalesStats::class)
        ->set('filters.brands', [$this->brandB->id])
        ->callAction('export');

    Excel::assertDownloaded('ventes-2026-01-01-2026-11-09.xlsx', function (SalesExport $export): bool {
        $rows = $export->collection();

        return 1 === $rows->count() && 'REF-B' === $rows->first()['Référence'] && 60.0 === $rows->first()['CA HT (€)'];
    });
});
