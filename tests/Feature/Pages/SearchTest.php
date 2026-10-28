<?php

declare(strict_types=1);

use App\Models\Item;
use Symfony\Component\HttpFoundation\Response;

it('redirects to the item when the reference matches a single item', function (): void {
    $item = createItems(1)->first();

    $this->get(uri: route('search', ['search-input' => $item->reference]))
        ->assertRedirect(route('item', ['item' => $item->slug]));
});

it('redirects to the master item when searching a variant reference', function (): void {
    $master = createItems(1)->first();
    $master->update(['reference' => 'B1232 Brown-Grey']);
    Item::factory()->create([
        'reference' => 'B1232 Brown-Grey 18mm',
        'brand_id' => $master->brand_id,
        'category_id' => $master->category_id,
        'master_id' => $master->id,
    ]);

    $this->get(uri: route('search', ['search-input' => 'Brown-Grey 18mm']))
        ->assertRedirect(route('item', ['item' => $master->fresh()->slug]));
});

it('lists items of a brand when searching its name', function (): void {
    $items = createItems(2);

    $this->get(uri: route('search', ['search-input' => $items->first()->brand->name]))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee($items->first()->reference)
        ->assertSee($items->last()->reference);
});

it('shows an empty result page instead of redirecting home', function (): void {
    createItems(1);

    $this->get(uri: route('search', ['search-input' => 'no-such-reference']))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee(__('listing.search.no_result'));
});
