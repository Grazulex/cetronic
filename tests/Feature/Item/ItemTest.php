<?php

declare(strict_types=1);


use App\Http\Livewire\FormItem;
use App\Models\CategoryTranslation;

use function Pest\Livewire\livewire;

use Symfony\Component\HttpFoundation\Response;

it('can see item in en', function (): void {
    $item = createItems(1)->first();
    $this->get(uri: route('item', $item, ['slug' => $item->slug]))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee($item->name)
        ->assertSee($item->description)
        ->assertSee($item->category->name)
        ->assertSee($item->brand->name);
});

it('can see item in fr', function (): void {
    $this->refreshApplicationWithLocale('fr');
    $item = createItems(1)->first();
    $translation = CategoryTranslation::where('category_id', $item->category_id)->where('locale', 'fr')->pluck('name')->first();
    $this->get(uri: route('item', $item, ['slug' => $item->slug]))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee($item->name)
        ->assertSee($item->description)
        ->assertSee($translation)
        ->assertSee($item->brand->name);
});


it('can see item in nl', function (): void {
    $this->refreshApplicationWithLocale('nl');
    $item = createItems(1)->first();
    $translation = CategoryTranslation::where('category_id', $item->category_id)->where('locale', 'nl')->pluck('name')->first();
    $this->get(uri: route('item', $item, ['slug' => $item->slug]))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee($item->name)
        ->assertSee($item->description)
        ->assertSee($translation)
        ->assertSee($item->brand->name);
});


it('can see item multiple', function (): void {
    $item = createItems(1)->first();
    $this->get(uri: route('item', $item, ['slug' => $item->slug]))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee($item->name)
        ->assertSee($item->description)
        ->assertSee($item->category->name)
        ->assertSee($item->brand->name)
        ->assertSee($item->multiple_quantity);
    livewire(FormItem::class, ['item' => $item, 'quantity' => [$item->multiple_quantity], 'variante' => ['']])
        ->call('up')
        ->assertSee($item->multiple_quantity * 2);
});

it('can see medias', function (): void {
    Storage::disk('items')->put('tmp/default_1.jpg', file_get_contents(app_path('../tests/Assets/Pictures/default.jpg')));
    Storage::disk('items')->put('tmp/default_2.jpg', file_get_contents(app_path('../tests/Assets/Pictures/default.jpg')));
    $item = createItems(1)->first();
    $item->addMediaFromDisk('tmp/default_1.jpg', 'items')->toMediaCollection();
    $item->addMediaFromDisk('tmp/default_2.jpg', 'items')->toMediaCollection();
    $this->get(uri: route('item', $item, ['slug' => $item->slug]))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee($item->getFirstMedia()->url)
        ->assertSee($item->getMedia()[1]->url);
});

it('can see variants', function (): void {})->todo();

it('can see metas', function (): void {})->todo();

it('can not order if not logged', function (): void {})->todo();

it('can order if logged', function (): void {})->todo();
