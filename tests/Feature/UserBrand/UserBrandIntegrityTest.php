<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\User;
use App\Models\UserBrand;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->customer = User::factory()->create(['role' => 'customer', 'email' => 'jane@example.com']);
    $this->brand = Brand::factory()->create(['name' => 'Alpha']);
    $this->category = Category::factory()->create(['name' => 'Rings']);
    $this->meta = CategoryMeta::factory()->create(['category_id' => $this->category->id]);
});

function insertRule(array $overrides = []): int
{
    return DB::table('user_brands')->insertGetId(array_merge([
        'user_id' => test()->customer->id,
        'brand_id' => test()->brand->id,
        'category_id' => test()->category->id,
        'reduction' => 1000,
        'coefficient' => 0,
        'addition_price' => 0,
        'price_type' => 'default',
        'not_show_promo' => false,
        'is_excluded' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides));
}

it('allows a category rule and a meta rule for the same user, brand and category', function (): void {
    insertRule();
    insertRule(['category_meta_id' => $this->meta->id, 'category_meta_value' => 'Gold', 'reduction' => 2000]);

    expect(UserBrand::count())->toBe(2);
});

it('stores rounded hundredths on user brands', function (): void {
    $rule = new UserBrand([
        'user_id' => $this->customer->id,
        'brand_id' => $this->brand->id,
        'category_id' => $this->category->id,
    ]);
    $rule->reduction = 12.35;
    $rule->coefficient = 1.15;
    $rule->addition_price = 0.29;
    $rule->saveQuietly();

    expect($rule->getRawOriginal('reduction'))->toBe(1235)
        ->and($rule->getRawOriginal('coefficient'))->toBe(115)
        ->and($rule->getRawOriginal('addition_price'))->toBe(29);
});

it('stores rounded hundredths on users', function (): void {
    $this->customer->franco = 150.10;
    $this->customer->shipping_price = 12.35;

    expect($this->customer->getAttributes()['franco'])->toBe(15010)
        ->and($this->customer->getAttributes()['shipping_price'])->toBe(1235);
});

it('deletes identical duplicates keeping the lowest id', function (): void {
    $keep = insertRule();
    insertRule();
    insertRule();

    $this->artisan('user-brands:dedupe')->assertSuccessful();

    expect(UserBrand::pluck('id')->all())->toBe([$keep]);
});

it('leaves conflicting duplicates untouched and reports them', function (): void {
    insertRule(['reduction' => 1000]);
    insertRule(['reduction' => 2000]);

    $this->artisan('user-brands:dedupe')
        ->expectsOutputToContain('jane@example.com')
        ->assertSuccessful();

    expect(UserBrand::count())->toBe(2);
});

it('ignores meta rules when deduplicating', function (): void {
    insertRule();
    insertRule(['category_meta_id' => $this->meta->id, 'category_meta_value' => 'Gold']);
    insertRule(['category_meta_id' => $this->meta->id, 'category_meta_value' => 'Silver']);

    $this->artisan('user-brands:dedupe')->assertSuccessful();

    expect(UserBrand::count())->toBe(3);
});

it('changes nothing with --dry-run', function (): void {
    insertRule();
    insertRule();

    $this->artisan('user-brands:dedupe', ['--dry-run' => true])->assertSuccessful();

    expect(UserBrand::count())->toBe(2);
});
