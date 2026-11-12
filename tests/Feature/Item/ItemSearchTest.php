<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Item;

beforeEach(function (): void {
    $this->brand = Brand::factory()->create(['name' => 'Alpha']);
    $this->watches = Category::factory()->create(['name' => 'Watches']);
    $this->accessories = Category::factory()->create(['name' => 'Accessories']);
    CategoryTranslation::create(['category_id' => $this->watches->id, 'locale' => 'fr', 'name' => 'Montres', 'slug' => 'montres']);
    CategoryTranslation::create(['category_id' => $this->watches->id, 'locale' => 'nl', 'name' => 'Horloges', 'slug' => 'horloges']);

    Item::factory()->create(['reference' => 'W-100', 'brand_id' => $this->brand->id, 'category_id' => $this->watches->id, 'description' => 'Steel chronograph']);
    Item::factory()->create(['reference' => 'A-200', 'brand_id' => $this->brand->id, 'category_id' => $this->accessories->id, 'description' => 'High quality leather wallet']);
});

function searchReferences(string $term): array
{
    return Item::matchingTerm($term)->orderBy('reference')->pluck('reference')->all();
}

it('still finds items by reference and brand', function (): void {
    expect(searchReferences('W-10'))->toBe(['W-100'])
        ->and(searchReferences('alpha'))->toBe(['A-200', 'W-100']);
});

it('finds items by a word of their description', function (): void {
    expect(searchReferences('wallet'))->toBe(['A-200'])
        ->and(searchReferences('chronograph'))->toBe(['W-100']);
});

it('finds items by category name in any language', function (string $term): void {
    expect(searchReferences($term))->toBe(['W-100']);
})->with(['Watches', 'montre', 'horloge']);

it('returns nothing for an unrelated word', function (): void {
    expect(searchReferences('umbrella'))->toBe([]);
});
