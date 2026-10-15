<?php

declare(strict_types=1);

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function storeCategoryPicture(string $name = 'bands.png'): string
{
    return UploadedFile::fake()->image($name, 900, 450)->storeAs('categories', $name, 'public');
}

beforeEach(function (): void {
    Storage::fake('public');
});

it('generates a webp version when a category picture is saved', function (): void {
    $category = Category::factory()->create(['picture' => storeCategoryPicture()]);

    Storage::disk('public')->assertExists('categories/bands.webp');
    expect(getimagesize(Storage::disk('public')->path('categories/bands.webp'))['mime'])->toBe('image/webp');

    $category->update(['picture' => storeCategoryPicture('watches.png')]);

    Storage::disk('public')->assertExists('categories/watches.webp');
});

it('serves the webp version of the category picture when available', function (): void {
    $category = Category::factory()->create(['picture' => storeCategoryPicture()]);

    expect((new CategoryService())->getDefaultPicture($category))
        ->toBe(Storage::url('categories/bands.webp'));
});

it('falls back to the original category picture when no webp version exists', function (): void {
    $category = Category::factory()->create(['picture' => storeCategoryPicture()]);
    Storage::disk('public')->delete('categories/bands.webp');

    expect((new CategoryService())->getDefaultPicture($category))
        ->toBe(Storage::url('categories/bands.png'));
});

it('backfills webp versions for existing category pictures', function (): void {
    $category = Category::factory()->create(['picture' => storeCategoryPicture()]);
    Storage::disk('public')->delete('categories/bands.webp');

    $this->artisan('categories:generate-webp')->assertSuccessful();

    Storage::disk('public')->assertExists('categories/bands.webp');
});
