<?php

declare(strict_types=1);

use App\Livewire\FormItem;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;

use function Pest\Livewire\livewire;

it('renders well-formed quantity buttons', function (): void {
    $item = Item::factory()->create([
        'brand_id' => Brand::factory()->create()->id,
        'category_id' => Category::factory()->create()->id,
    ]);
    loginAsUser();

    $html = livewire(FormItem::class, ['item' => $item, 'quantity' => [$item->multiple_quantity], 'variante' => ['']])->html();

    expect($html)->toContain('wire:click="down(' . $item->id . ')"')
        ->toContain('wire:click="up(' . $item->id . ')"')
        ->toMatch('/<span wire:click="up\(' . $item->id . '\)"\s+class="border-0 bg-body plus">\+<\/span>/');
});
