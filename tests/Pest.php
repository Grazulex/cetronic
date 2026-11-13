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

function loginAsUser(?User $user = null): User
{
    $user ??= User::factory()->create([
        'role' => UserRoleEnum::CUSTOMER,
        'is_actif' => true,
    ]);
    actingAs($user);

    return $user;
}

function loginAsAdmin(?User $user = null): User
{
    $user ??= User::factory()->create([
        'role' => UserRoleEnum::ADMIN,
        'is_actif' => true,
    ]);
    actingAs($user);

    return $user;
}

function loginAsAgent(?User $user = null): User
{
    $user ??= User::factory()->create([
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
    CategoryTranslation::factory()->count(1)->create(['category_id' => $category->first()->id, 'locale' => 'fr', 'name' => 'FR-' . $category->first()->name]);
    CategoryTranslation::factory()->count(1)->create(['category_id' => $category->first()->id, 'locale' => 'nl', 'name' => 'NL-' . $category->first()->name]);
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
        $price = $i * 10;
        $items[] = Item::factory()->create(['brand_id' => Brand::first()->id, 'category_id' => $category->first()->id, 'is_published' => true, 'price' => $price, 'price_b2b' => $price - 1, 'price_promo' => $price / 2, 'price_special1' => $price - 2, 'price_special2' => $price - 3, 'price_special3' => $price - 4, 'sale_price' => $price - 5]);
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

/**
 * Creates one order holding one line. The unit price is given in euros.
 *
 * @param  array<string, mixed>  $order
 */
function createSale(Item $item, int $quantity, float $unitPrice, string $date, array $order = [], string $name = 'Line'): App\Models\Order
{
    $order = App\Models\Order::factory()->create(array_merge(['created_at' => Illuminate\Support\Carbon::parse($date)], $order));
    App\Models\OrderItem::factory()->create([
        'order_id' => $order->id,
        'item_id' => $item->id,
        'quantity' => $quantity,
        'price_paid' => $unitPrice,
        'name' => $name,
    ]);

    return $order;
}

uses(Tests\TestCase::class)->in('Unit/SiteTexts', 'Unit/Stats');

/**
 * Inserts one phrase in the ltu_* translation tables (site texts), creating the
 * language, its translation and the file when needed. French and Dutch phrases
 * are linked to the English (source) phrase of the same key, like the package did.
 */
function ltuPhrase(string $locale, string $file, string $key, ?string $value, string $extension = 'php'): int
{
    $db = Illuminate\Support\Facades\DB::table(...);

    $languageId = $db('ltu_languages')->where('code', $locale)->value('id')
        ?? $db('ltu_languages')->insertGetId(['name' => mb_strtoupper($locale), 'code' => $locale]);
    $translationId = $db('ltu_translations')->where('language_id', $languageId)->value('id')
        ?? $db('ltu_translations')->insertGetId(['language_id' => $languageId, 'source' => 'en' === $locale]);
    $fileId = $db('ltu_translation_files')->where('name', $file)->where('extension', $extension)->value('id')
        ?? $db('ltu_translation_files')->insertGetId(['name' => $file, 'extension' => $extension]);

    $sourceId = 'en' === $locale ? null : $db('ltu_phrases')
        ->join('ltu_translations', 'ltu_translations.id', '=', 'ltu_phrases.translation_id')
        ->join('ltu_languages', 'ltu_languages.id', '=', 'ltu_translations.language_id')
        ->where('ltu_languages.code', 'en')
        ->where('ltu_phrases.translation_file_id', $fileId)
        ->where('ltu_phrases.key', $key)
        ->value('ltu_phrases.id');

    return $db('ltu_phrases')->insertGetId([
        'uuid' => (string) Illuminate\Support\Str::uuid(),
        'translation_id' => $translationId,
        'translation_file_id' => $fileId,
        'phrase_id' => $sourceId,
        'key' => $key,
        'group' => $file,
        'value' => $value,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/**
 * Points lang_path() to a fresh temporary directory, so exports never touch lang/.
 */
function useTemporaryLangPath(): string
{
    $path = sys_get_temp_dir() . '/cetronic-lang-' . Illuminate\Support\Str::random(8);
    Illuminate\Support\Facades\File::ensureDirectoryExists($path);
    app()->useLangPath($path);

    return $path;
}
