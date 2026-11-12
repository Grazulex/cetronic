<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\Item;
use App\Models\ItemMeta;

beforeEach(function (): void {
    $this->brand = Brand::factory()->create(['name' => 'Alpha', 'slug' => 'alpha']);
    $this->category = Category::factory()->create(['name' => 'Watches', 'slug' => 'watches']);
    $this->colour = CategoryMeta::factory()->create(['category_id' => $this->category->id, 'name' => 'couleur boitier', 'is_meta' => true, 'is_color' => false, 'is_variant' => true]);
    $this->gender = CategoryMeta::factory()->create(['category_id' => $this->category->id, 'name' => 'genre', 'is_meta' => true, 'is_color' => false, 'is_variant' => true]);

    $colours = ['Black', 'Blue', 'Gold', 'Green', 'Grey', 'Rose Gold', 'Silver', 'White'];
    foreach ($colours as $index => $colour) {
        $item = Item::factory()->create(['brand_id' => $this->brand->id, 'category_id' => $this->category->id, 'is_published' => true, 'reference' => "REF-{$index}", 'master_id' => null]);
        ItemMeta::factory()->create(['item_id' => $item->id, 'meta_id' => $this->colour->id, 'value' => $colour]);
        ItemMeta::factory()->create(['item_id' => $item->id, 'meta_id' => $this->gender->id, 'value' => 0 === $index % 2 ? 'Gents' : 'Ladies']);
    }
    loginAsUser();
});

function listingUrl(array $query = []): string
{
    return route('list', ['cat' => 'watches', 'type' => 'category', 'slug' => 'watches']) . ($query ? '?' . http_build_query($query) : '');
}

it('collapses filter groups unless they hold an active filter', function (): void {
    $html = $this->get(listingUrl(['metas' => [$this->gender->id => ['Gents']]]))->assertOk()->getContent();

    expect($html)->toMatch('/<details class="filter-group" data-meta="' . $this->colour->id . '">/')
        ->toMatch('/<details class="filter-group" data-meta="' . $this->gender->id . '" open>/');
});

it('shows counts without the word items and hides values beyond six behind a toggle', function (): void {
    $html = $this->get(listingUrl())->assertOk()->getContent();

    expect($html)->not->toContain('items)')
        ->toContain('class="filter-option_count">1<')
        ->and(mb_substr_count($html, 'filter-option is-extra'))->toBe(2)
        ->and($html)->toContain('data-filter-more');
});

it('moves sorting and page size above the products with the result count', function (): void {
    $html = $this->get(listingUrl())->assertOk()->getContent();

    expect($html)->toContain('id="listing-filters"')
        ->toMatch('/<select[^>]*name="order"[^>]*form="listing-filters"/')
        ->toMatch('/<select[^>]*name="paginate"[^>]*form="listing-filters"/')
        ->toContain(trans_choice('listing.results', 8, ['count' => 8]));
});

it('lists active filters as removable chips', function (): void {
    $html = $this->get(listingUrl(['metas' => [$this->colour->id => ['Gold', 'Silver']], 'new' => '1']))->assertOk()->getContent();

    $withoutGold = e(listingUrl(['metas' => [$this->colour->id => ['Silver']], 'new' => '1']));

    expect($html)->toContain('class="filter-chip"')
        ->toContain('href="' . $withoutGold . '"')
        ->toContain('href="' . e(listingUrl()) . '"');
});

it('redirects home when the category in the url does not exist', function (): void {
    $this->get(route('list', ['cat' => 'unknown', 'type' => 'brand', 'slug' => 'alpha']))->assertRedirect(route('home'));
    $this->get(route('list', ['cat' => 'unknown', 'type' => 'category', 'slug' => 'watches']))->assertRedirect(route('home'));
});
