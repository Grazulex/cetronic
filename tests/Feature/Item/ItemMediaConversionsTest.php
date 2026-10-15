<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Services\ItemService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob;

function createItemWithPicture(): Item
{
    Storage::fake('items');

    $item = Item::factory()->create([
        'brand_id' => Brand::factory()->create()->id,
        'category_id' => Category::factory()->create()->id,
        'is_published' => true,
    ]);

    $item->addMedia(UploadedFile::fake()->image('picture.jpg', 1600, 2400))
        ->toMediaCollection(collectionName: 'default', diskName: 'items');

    return $item->fresh();
}

it('registers thumb and medium webp conversions', function (): void {
    $media = createItemWithPicture()->getFirstMedia();

    expect($media->hasGeneratedConversion('thumb'))->toBeTrue()
        ->and($media->hasGeneratedConversion('medium'))->toBeTrue()
        ->and($media->getUrl('thumb'))->toEndWith('-thumb.webp')
        ->and($media->getUrl('medium'))->toEndWith('-medium.webp');

    [$width, $height] = getimagesize($media->getPath('medium'));
    expect(max($width, $height))->toBe(800);

    [$width, $height] = getimagesize($media->getPath('thumb'));
    expect(max($width, $height))->toBe(400);
});

it('serves the medium conversion as default picture', function (): void {
    $item = createItemWithPicture();

    expect((new ItemService($item))->getDefaultPicture())
        ->toBe($item->getFirstMedia()->getUrl('medium'));
});

it('falls back to the original picture when conversions are not generated yet', function (): void {
    $item = createItemWithPicture();
    $media = $item->getFirstMedia();
    $media->markAsConversionNotGenerated('thumb');
    $media->markAsConversionNotGenerated('medium');

    expect((new ItemService($item->fresh()))->getDefaultPicture())
        ->toBe($media->getUrl());
});

it('dispatches picture conversions on the dedicated media queue', function (): void {
    Queue::fake();

    createItemWithPicture();

    Queue::assertPushedOn('media', PerformConversionsJob::class);
});

it('renders product cards with the medium picture and thumbnails with the thumb picture', function (): void {
    $item = createItemWithPicture();
    $item->addMedia(UploadedFile::fake()->image('second.jpg', 1200, 1200))
        ->toMediaCollection(collectionName: 'default', diskName: 'items');
    $item = $item->fresh();

    $html = Blade::render('<x-items.picture3-component :item="$item" />', ['item' => $item]);

    foreach ($item->getMedia() as $media) {
        expect($html)
            ->toContain("background-image: url('" . $media->getUrl('medium') . "')")
            ->toContain('src="' . $media->getUrl('thumb') . '"')
            ->not->toContain("url('" . $media->getUrl() . "')");
    }
});
