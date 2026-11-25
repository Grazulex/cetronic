<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\CategoryMetaTranslation;
use App\Models\CategoryTranslation;
use Illuminate\Database\UniqueConstraintViolationException;

/*
 * A word can be spelled the same in French and Dutch ("Accessoires"):
 * translation slugs only have to be unique within one language.
 */

it('accepts the same category translation in two languages', function (): void {
    $category = Category::factory()->create(['name' => 'Accessories']);

    CategoryTranslation::create(['category_id' => $category->id, 'locale' => 'fr', 'name' => 'Accessoires']);
    $nl = CategoryTranslation::create(['category_id' => $category->id, 'locale' => 'nl', 'name' => 'Accessories']);

    $nl->update(['name' => 'Accessoires']);

    expect($nl->fresh()->slug)->toBe('accessoires')
        ->and(CategoryTranslation::where('slug', 'accessoires')->count())->toBe(2);
});

it('still refuses the same category translation slug twice in one language', function (): void {
    $category = Category::factory()->create();

    CategoryTranslation::create(['category_id' => $category->id, 'locale' => 'fr', 'name' => 'Accessoires']);
    CategoryTranslation::create(['category_id' => $category->id, 'locale' => 'fr', 'name' => 'Accessoires']);
})->throws(UniqueConstraintViolationException::class);

it('accepts the same category meta translation in two languages', function (): void {
    $meta = CategoryMeta::factory()->create(['category_id' => Category::factory()->create()->id]);

    CategoryMetaTranslation::create(['category_meta_id' => $meta->id, 'locale' => 'fr', 'name' => 'Couleur']);
    CategoryMetaTranslation::create(['category_meta_id' => $meta->id, 'locale' => 'nl', 'name' => 'Couleur']);

    expect(CategoryMetaTranslation::where('slug', 'couleur')->count())->toBe(2);
});
