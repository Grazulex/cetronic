<?php

declare(strict_types=1);

use App\Http\Livewire\SearchItem;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;

use function Pest\Livewire\livewire;

/*
 * Instant search in the header. In Livewire 3, wire:model becomes deferred:
 * the input must switch to wire:model.live or these previews stop updating
 * while typing (the component tests below would still pass, so check in a browser).
 */

beforeEach(function (): void {
    $brand = Brand::factory()->create(['name' => 'Zenith', 'is_published' => true]);
    $category = Category::factory()->create(['is_published' => true]);

    $this->visible = Item::factory()->create(['reference' => 'ZX-1001', 'brand_id' => $brand->id, 'category_id' => $category->id, 'is_published' => true]);
    $this->hidden = Item::factory()->create(['reference' => 'ZX-2002', 'brand_id' => $brand->id, 'category_id' => $category->id, 'is_published' => false]);
});

it('shows nothing while the term is too short', function (): void {
    livewire(SearchItem::class)
        ->set('term', 'ZX')
        ->assertDontSee('ZX-1001');
});

it('previews published items matching the term', function (): void {
    loginAsUser();

    livewire(SearchItem::class)
        ->set('term', 'ZX-')
        ->assertSee('Zenith - ZX-1001')
        ->assertDontSee('ZX-2002');
});

it('also matches on the brand name', function (): void {
    livewire(SearchItem::class)
        ->set('term', 'zenit')
        ->assertSee('ZX-1001');
});
