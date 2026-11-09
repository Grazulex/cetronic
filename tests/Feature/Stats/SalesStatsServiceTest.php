<?php

declare(strict_types=1);

use App\DataObjects\Stats\SalesFilters;
use App\Enum\OrderStatusEnum;
use App\Enum\SalesGroupByEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use App\Services\SalesStatsService;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    $this->brandA = Brand::factory()->create(['name' => 'Alpha']);
    $this->brandB = Brand::factory()->create(['name' => 'Beta']);
    $this->catX = Category::factory()->create(['name' => 'Rings']);
    $this->catY = Category::factory()->create(['name' => 'Watches']);

    $this->itemA = Item::factory()->create(['reference' => 'REF-A', 'brand_id' => $this->brandA->id, 'category_id' => $this->catX->id]);
    $this->itemB = Item::factory()->create(['reference' => 'REF-B', 'brand_id' => $this->brandB->id, 'category_id' => $this->catY->id]);

    $this->service = app(SalesStatsService::class);
});

it('summarises revenue, quantity, orders and customers in euros', function (): void {
    $customer = User::factory()->create(['role' => 'customer']);
    createSale($this->itemA, 3, 12.35, '2026-03-10', ['user_id' => $customer->id]);
    createSale($this->itemB, 2, 5.10, '2026-04-02', ['user_id' => $customer->id]);
    createSale($this->itemA, 1, 7.00, '2026-04-05');

    $summary = $this->service->summary(new SalesFilters());

    expect($summary['revenue'])->toBe(54.25)
        ->and($summary['quantity'])->toBe(6)
        ->and($summary['orders'])->toBe(3)
        ->and($summary['customers'])->toBe(2)
        ->and($summary['average_order'])->toBe(18.08);
});

it('counts every status but ignores deleted orders', function (): void {
    createSale($this->itemA, 1, 10, '2026-03-10', ['status' => OrderStatusEnum::OPEN->value]);
    createSale($this->itemA, 1, 20, '2026-03-10', ['status' => OrderStatusEnum::IN_PREPARATION->value]);
    createSale($this->itemA, 1, 40, '2026-03-10', ['status' => OrderStatusEnum::SHIPPED->value]);
    createSale($this->itemA, 1, 80, '2026-03-10')->delete();

    expect($this->service->summary(new SalesFilters())['revenue'])->toBe(70.0);
});

it('keeps the sales of products moved to the trash', function (): void {
    createSale($this->itemA, 2, 10, '2026-03-10');
    $this->itemA->delete();

    expect($this->service->summary(new SalesFilters())['revenue'])->toBe(20.0);
});

it('filters on the order date, end day included', function (): void {
    createSale($this->itemA, 1, 1, '2026-01-31 23:00:00');
    createSale($this->itemA, 1, 10, '2026-02-01 08:00:00');
    createSale($this->itemA, 1, 100, '2026-02-28 23:59:00');
    createSale($this->itemA, 1, 1000, '2026-03-01 00:00:00');

    $filters = new SalesFilters(from: Carbon::parse('2026-02-01'), to: Carbon::parse('2026-02-28'));

    expect($this->service->summary($filters)['revenue'])->toBe(110.0);
});

it('filters on brands, categories and products', function (): void {
    createSale($this->itemA, 1, 10, '2026-03-10');
    createSale($this->itemB, 1, 100, '2026-03-10');

    expect($this->service->summary(new SalesFilters(brandIds: [$this->brandB->id]))['revenue'])->toBe(100.0)
        ->and($this->service->summary(new SalesFilters(categoryIds: [$this->catX->id]))['revenue'])->toBe(10.0)
        ->and($this->service->summary(new SalesFilters(itemId: $this->itemB->id))['revenue'])->toBe(100.0);
});

it('searches on the product reference and the ordered name', function (): void {
    createSale($this->itemA, 1, 10, '2026-03-10', [], 'Golden ring');
    createSale($this->itemB, 1, 100, '2026-03-10', [], 'Steel watch');

    expect($this->service->summary(new SalesFilters(search: 'ref-b'))['revenue'])->toBe(100.0)
        ->and($this->service->summary(new SalesFilters(search: 'golden'))['revenue'])->toBe(10.0);
});

it('filters on customer and agent', function (): void {
    $customer = User::factory()->create(['role' => 'customer']);
    $agent = User::factory()->create(['role' => 'agent']);
    createSale($this->itemA, 1, 10, '2026-03-10', ['user_id' => $customer->id]);
    createSale($this->itemA, 1, 100, '2026-03-10', ['agent_id' => $agent->id]);

    expect($this->service->summary(new SalesFilters(userId: $customer->id))['revenue'])->toBe(10.0)
        ->and($this->service->summary(new SalesFilters(agentId: $agent->id))['revenue'])->toBe(100.0);
});

it('returns an empty summary without sales', function (): void {
    $summary = $this->service->summary(new SalesFilters());

    expect($summary['revenue'])->toBe(0.0)
        ->and($summary['orders'])->toBe(0)
        ->and($summary['average_order'])->toBe(0.0);
});

it('groups sales per month and fills the empty months', function (): void {
    createSale($this->itemA, 2, 10, '2026-01-15');
    createSale($this->itemA, 1, 5, '2026-01-20');
    createSale($this->itemA, 4, 1, '2026-03-02');

    $months = $this->service->monthly(new SalesFilters(from: Carbon::parse('2026-01-01'), to: Carbon::parse('2026-04-30')));

    expect($months)->toBe([
        ['month' => '2026-01', 'revenue' => 25.0, 'quantity' => 3],
        ['month' => '2026-02', 'revenue' => 0.0, 'quantity' => 0],
        ['month' => '2026-03', 'revenue' => 4.0, 'quantity' => 4],
        ['month' => '2026-04', 'revenue' => 0.0, 'quantity' => 0],
    ]);
});

it('breaks sales down per product, brand and category', function (SalesGroupByEnum $groupBy, array $expected): void {
    createSale($this->itemA, 2, 10, '2026-03-10');
    createSale($this->itemA, 1, 10, '2026-03-11');
    createSale($this->itemB, 1, 50, '2026-03-12');

    $rows = $this->service->breakdownQuery(new SalesFilters(), $groupBy)
        ->orderByDesc('revenue')
        ->get()
        ->map(fn($row) => [$row->label, (int) $row->quantity, (int) $row->revenue, (int) $row->orders_count, (int) $row->products_count])
        ->all();

    expect($rows)->toBe($expected);
})->with([
    'product' => [SalesGroupByEnum::ITEM, [['REF-B', 1, 5000, 1, 1], ['REF-A', 3, 3000, 2, 1]]],
    'brand' => [SalesGroupByEnum::BRAND, [['Beta', 1, 5000, 1, 1], ['Alpha', 3, 3000, 2, 1]]],
    'category' => [SalesGroupByEnum::CATEGORY, [['Watches', 1, 5000, 1, 1], ['Rings', 3, 3000, 2, 1]]],
]);

it('lists the best customers', function (): void {
    $small = User::factory()->create(['role' => 'customer', 'name' => 'Small']);
    $big = User::factory()->create(['role' => 'customer', 'name' => 'Big']);
    createSale($this->itemA, 1, 10, '2026-03-10', ['user_id' => $small->id]);
    createSale($this->itemA, 5, 10, '2026-03-10', ['user_id' => $big->id]);
    createSale($this->itemA, 1, 10, '2026-03-11', ['user_id' => $big->id]);

    $top = $this->service->topCustomers(new SalesFilters(), 10);

    expect($top->pluck('name')->all())->toBe(['Big', 'Small'])
        ->and($top->first()->role)->toBe('customer')
        ->and($top->first()->revenue)->toBe(60.0)
        ->and($top->first()->quantity)->toBe(6)
        ->and($top->first()->orders_count)->toBe(2);
});

it('builds filters from the page form state', function (): void {
    $filters = SalesFilters::fromArray([
        'from' => '2026-01-01',
        'to' => '2026-06-30',
        'brands' => ['3', 5],
        'categories' => [],
        'search' => '  ',
        'user_id' => '7',
        'agent_id' => null,
    ]);

    expect($filters->from->toDateString())->toBe('2026-01-01')
        ->and($filters->to->toDateString())->toBe('2026-06-30')
        ->and($filters->brandIds)->toBe([3, 5])
        ->and($filters->search)->toBeNull()
        ->and($filters->userId)->toBe(7)
        ->and($filters->agentId)->toBeNull();
});
