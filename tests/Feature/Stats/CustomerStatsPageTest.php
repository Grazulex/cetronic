<?php

declare(strict_types=1);

use App\Enum\OrderStatusEnum;
use App\Filament\Resources\UserResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow('2026-11-09 10:00:00');
    Brand::factory()->create(['name' => 'Alpha']);
    Category::factory()->create(['name' => 'Rings']);
    $this->item = Item::factory()->create(['reference' => 'REF-A']);
    $this->customer = User::factory()->create(['role' => 'customer', 'name' => 'Bijouterie Dupont']);
});

afterEach(fn() => Carbon::setTestNow());

function customerStatsUrl(User $customer): string
{
    return UserResource::getUrl('customer-stats', ['record' => $customer]);
}

it('counts the orders of every status in the totals', function (): void {
    loginAsAdmin();
    createSale($this->item, 1, 100, '2026-03-01', ['user_id' => $this->customer->id, 'status' => OrderStatusEnum::OPEN->value]);
    createSale($this->item, 1, 40, '2026-03-02', ['user_id' => $this->customer->id, 'status' => OrderStatusEnum::IN_PREPARATION->value]);

    $this->get(customerStatsUrl($this->customer))
        ->assertOk()
        ->assertSeeInOrder(['Commandes', '2'])
        ->assertSee('140,00 €')
        ->assertSee('70,00 €');
});

it('shows the VAT-included total recorded on the order instead of a 21% estimate', function (): void {
    loginAsAdmin();
    // Intra-community customer: no VAT on the order.
    createSale($this->item, 1, 100, '2026-03-01', [
        'user_id' => $this->customer->id,
        'total_price' => 100,
        'total_price_with_tax' => 100,
    ]);

    $this->get(customerStatsUrl($this->customer))
        ->assertOk()
        ->assertDontSee('121,00 €');
});

it('shows the monthly evolution of the customer', function (): void {
    loginAsAdmin();
    createSale($this->item, 1, 100, '2026-03-01', ['user_id' => $this->customer->id]);

    $this->get(customerStatsUrl($this->customer))
        ->assertOk()
        ->assertSee('Évolution mensuelle');
});

it('charts the whole history of the customer, like the key figures', function (): void {
    loginAsAdmin();
    createSale($this->item, 1, 100, '2023-08-01', ['user_id' => $this->customer->id]);

    $html = $this->get(customerStatsUrl($this->customer))->assertOk()->getContent();

    // The chart labels are JSON-encoded in the page: the escaping of "/" varies.
    expect($html)
        ->toMatch('#08\\\\*/2023#')
        ->toMatch('#11\\\\*/2026#');
});

it('breaks the customer sales down per brand and category', function (): void {
    loginAsAdmin();
    createSale($this->item, 3, 10, '2026-03-01', ['user_id' => $this->customer->id]);

    $this->get(customerStatsUrl($this->customer))
        ->assertOk()
        ->assertSeeInOrder(['Alpha', '3', '30,00 €'])
        ->assertSeeInOrder(['Rings', '3', '30,00 €']);
});

it('lays the customer key figures out in a grid', function (): void {
    loginAsAdmin();

    $this->get(customerStatsUrl($this->customer))
        ->assertOk()
        ->assertSee('grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr))', false);
});
