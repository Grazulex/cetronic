<?php

declare(strict_types=1);

use App\Filament\Resources\ItemResource\Pages\CreateItem;
use App\Filament\Resources\ItemResource\Pages\EditItem;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Brand::factory()->create();
    Category::factory()->create();
    loginAsAdmin();
});

it('shows the product form tabs and the publication panel', function (): void {
    $item = Item::factory()->create();

    livewire(EditItem::class, ['record' => $item->getRouteKey()])
        ->assertOk()
        ->assertSee(['Général', 'Prix', 'Photos', 'Ventes', 'Publication', 'En bref', 'En ligne', 'Nouveauté', 'Best-seller']);
});

it('titles the metas relation manager "Caractéristiques"', function (): void {
    $item = Item::factory()->create();

    // Relation managers load lazily: read the title the page will show.
    expect(App\Filament\Resources\ItemResource\RelationManagers\MetasRelationManager::getTitle($item, EditItem::class))
        ->toBe('Caractéristiques');
});

it('still saves every field', function (): void {
    $item = Item::factory()->create(['is_published' => false, 'multiple_quantity' => 1]);

    livewire(EditItem::class, ['record' => $item->getRouteKey()])
        ->fillForm(['price_b2b' => 15, 'is_published' => true, 'multiple_quantity' => 6, 'catalog_group' => 'Réveils'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($item->fresh())
        ->is_published->toBeTrue()
        ->multiple_quantity->toBe(6)
        ->price_b2b->toEqual(15)
        ->catalog_group->toBe('Réveils');
});

it('opens the create form without the summary failing', function (): void {
    livewire(CreateItem::class)->assertOk()->assertSee(['Général', 'Publication'])->assertDontSee('En bref');
});
