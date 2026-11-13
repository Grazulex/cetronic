<?php

declare(strict_types=1);

use App\Filament\Resources\ItemResource\Pages\CreateItem;
use App\Filament\Resources\ItemResource\Pages\EditItem;
use App\Livewire\Admin\ItemSales;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Carbon;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Carbon::setTestNow('2026-11-09 10:00:00');
    Brand::factory()->create();
    Category::factory()->create();
    $this->item = Item::factory()->create(['reference' => 'REF-A']);
    $this->other = Item::factory()->create(['reference' => 'REF-B']);
});

afterEach(fn() => Carbon::setTestNow());

it('adds a sales tab to the product edit form only', function (): void {
    loginAsAdmin();

    livewire(EditItem::class, ['record' => $this->item->getRouteKey()])->assertSee('Ventes');
    livewire(CreateItem::class)->assertDontSee('Ventes');
});

it('shows the sales of the product for the current year by default', function (): void {
    loginAsAdmin();
    createSale($this->item, 2, 30, '2026-03-01');
    createSale($this->item, 1, 1000, '2025-06-01');
    createSale($this->other, 5, 99, '2026-03-01');

    livewire(ItemSales::class, ['itemId' => $this->item->id])
        ->assertSet('period', 'this_year')
        ->assertSee('60,00 €')
        ->assertDontSee('1 060,00 €')
        ->set('period', 'all')
        ->assertSee('1 060,00 €');
});

it('lists the best customers of the product', function (): void {
    loginAsAdmin();
    $customer = User::factory()->create(['role' => 'customer', 'name' => 'Bijouterie Dupont']);
    createSale($this->item, 2, 30, '2026-03-01', ['user_id' => $customer->id]);

    livewire(ItemSales::class, ['itemId' => $this->item->id])->assertSee('Bijouterie Dupont');
});

it('is reserved to admins', function (): void {
    loginAsAgent();

    livewire(ItemSales::class, ['itemId' => $this->item->id])->assertForbidden();
});
