<?php

declare(strict_types=1);

use App\Enum\UserRoleEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\CategoryTranslation;
use App\Models\Item;
use App\Models\ItemMeta;
use App\Models\User;

use Illuminate\Database\Eloquent\Collection;

use function Pest\Laravel\actingAs;

function loginAsUser(User $user=null): User
{
    $user = $user ?? User::factory()->create([
        'role' => UserRoleEnum::CUSTOMER,
        'is_actif' => true,
    ]);
    actingAs($user);

    return $user;
}

function loginAsAdmin(User $user=null): User
{
    $user = $user ?? User::factory()->create([
        'role' => UserRoleEnum::ADMIN,
        'is_actif' => true,
    ]);
    actingAs($user);

    return $user;
}

function loginAsAgent(User $user=null): User
{
    $user = $user ?? User::factory()->create([
        'role' => UserRoleEnum::AGENT,
        'is_actif' => true,
    ]);
    actingAs($user);

    return $user;
}

function createItems(int $count): Collection
{
    Brand::factory()->count(1)->create(['is_featured' => true]);
    $category = Category::factory()->count(1)->create();
    CategoryTranslation::factory()->count(1)->create(['category_id' => $category->first()->id, 'locale' => 'fr', 'name' => 'FR-'.$category->first()->name]);
    CategoryTranslation::factory()->count(1)->create(['category_id' => $category->first()->id, 'locale' => 'nl', 'name' => 'NL-'.$category->first()->name]);
    $categoryMeta = CategoryMeta::factory()->count(1)->create(['category_id' => $category->first()->id]);
    $items = Item::factory()->count($count)->create(['brand_id' => Brand::first()->id, 'category_id' => $category->first()->id, 'is_published' => true]);

    foreach ($items as $item) {
        ItemMeta::factory()->count(1)->create(['item_id' => $item->first()->id, 'meta_id' => $categoryMeta->first()->id, 'value' => 'test']);
    }
    return $items;
}

function createDefaultItems(): Illuminate\Support\Collection
{
    Brand::factory()->count(1)->create(['is_featured' => true]);
    $category = Category::factory()->count(1)->create();
    $categoryMeta = CategoryMeta::factory()->count(1)->create(['category_id' => $category->first()->id]);
    $i = 1;
    while ($i <= 10) {
        $price = $i*10;
        $items[] = Item::factory()->create(['brand_id' => Brand::first()->id, 'category_id' => $category->first()->id, 'is_published' => true, 'price' => $price, 'price_b2b' => $price-1, 'price_promo' => $price/2, 'price_special1' => $price-2, 'price_special2' => $price-3, 'price_special3' => $price-4, 'sale_price' => $price-5]);
        $i++;
    }

    foreach ($items as $item) {
        ItemMeta::factory()->count(1)->create(['item_id' => $item->first()->id, 'meta_id' => $categoryMeta->first()->id, 'value' => 'test']);
    }

    return collect($items);
}

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');
