<?php

declare(strict_types=1);

use App\Enum\PriceTypeEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use App\Models\UserBrand;
use App\Services\BrandService;
use App\Services\ItemService;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->brand = Brand::factory()->create(['name' => 'Alpha']);
    $this->category = Category::factory()->create(['name' => 'Watches', 'slug' => 'watches']);
    $base = ['brand_id' => $this->brand->id, 'category_id' => $this->category->id, 'is_published' => true, 'price' => 20, 'price_b2b' => 20, 'price_promo' => 0, 'price_fix' => 0];

    $this->fixed = Item::factory()->create([...$base, 'reference' => 'FIX-1', 'price_fix' => 8]);
    $this->personal = Item::factory()->create([...$base, 'reference' => 'PROMO-1', 'price_promo' => 12]);
    $this->regular = Item::factory()->create([...$base, 'reference' => 'REG-1']);

    $this->customer = User::factory()->create(['role' => 'customer', 'is_actif' => true]);
    $this->blocked = User::factory()->create(['role' => 'customer', 'is_actif' => true]);
    foreach ([[$this->customer, false], [$this->blocked, true]] as [$user, $hidePromo]) {
        $rule = new UserBrand([
            'user_id' => $user->id, 'brand_id' => $this->brand->id, 'category_id' => $this->category->id,
            'reduction' => 50, 'coefficient' => 0, 'addition_price' => 0, 'price_type' => PriceTypeEnum::DEFAULT,
            'not_show_promo' => $hidePromo, 'is_excluded' => false,
        ]);
        $rule->saveQuietly();
    }
});

function promoReferences(?User $user): array
{
    return Item::promotions($user)->orderBy('reference')->pluck('reference')->all();
}

it('tells a fixed price promotion from a promotion calculated for the customer', function (): void {
    expect((new ItemService($this->fixed))->promoType($this->customer))->toBe('fixed')
        ->and((new ItemService($this->personal))->promoType($this->customer))->toBe('personal')
        ->and((new ItemService($this->regular))->promoType($this->customer))->toBeNull();
});

it('has no promotion for guests or for customers who must not see promotions', function (): void {
    expect((new ItemService($this->fixed))->promoType(null))->toBeNull()
        ->and((new ItemService($this->fixed))->promoType($this->blocked))->toBeNull()
        ->and((new ItemService($this->personal))->promoType($this->blocked))->toBeNull();
});

it('selects both kinds of promotions for a customer', function (): void {
    expect(promoReferences($this->customer))->toBe(['FIX-1', 'PROMO-1'])
        ->and(promoReferences($this->blocked))->toBe([]);
});

it('mixes both kinds on the homepage carousel', function (): void {
    actingAs($this->customer);

    $html = (string) $this->blade('<x-home-item-promo-component qty="8" />');

    expect($html)->toContain('FIX-1')
        ->toContain('PROMO-1')
        ->not->toContain('REG-1')
        ->toContain(e(__('item.badge.promo_fixed')))
        ->toContain(e(__('item.badge.promo_personal')));
});

it('limits the homepage carousel to the requested quantity', function (): void {
    actingAs($this->customer);

    $html = (string) $this->blade('<x-home-item-promo-component qty="1" />');

    expect(mb_substr_count($html, 'class="card"'))->toBe(1);
});

it('lists both kinds on the category promotions page', function (): void {
    actingAs($this->customer);

    $this->get(route('promo', ['cat' => 'watches']))
        ->assertOk()
        ->assertSee('FIX-1')
        ->assertSee('PROMO-1')
        ->assertDontSee('REG-1');
});

it('shows the promotions menu link when a category only has calculated promotions', function (): void {
    $this->fixed->update(['price_fix' => 0]);
    actingAs($this->customer);

    expect((new BrandService())->getCategoriesWithPromos($this->customer))->toHaveKey($this->category->id)
        ->and((new BrandService())->getCategoriesWithPromos($this->blocked))->toBe([]);
});

it('does not strike the promo price when it is the final price', function (): void {
    $plain = User::factory()->create(['role' => 'customer', 'is_actif' => true]);
    $rule = new UserBrand([
        'user_id' => $plain->id, 'brand_id' => $this->brand->id, 'category_id' => $this->category->id,
        'reduction' => 0, 'coefficient' => 0, 'addition_price' => 0, 'price_type' => PriceTypeEnum::DEFAULT,
        'not_show_promo' => false, 'is_excluded' => false,
    ]);
    $rule->saveQuietly();
    actingAs($plain);

    $html = (string) $this->blade('<x-items.price-component :item="$item" />', ['item' => $this->personal]);

    expect(mb_substr_count($html, 'class="strike"'))->toBe(1)
        ->and(mb_substr_count($html, '12,00'))->toBe(1);
});

it('strikes the start and promo prices when the customer discount applies on the promo price', function (): void {
    actingAs($this->customer);

    $html = (string) $this->blade('<x-items.price-component :item="$item" />', ['item' => $this->personal]);

    expect(mb_substr_count($html, 'class="strike"'))->toBe(2)
        ->and($html)->toContain('6,00');
});

it('shows new and promotion in one ribbon under the picture', function (): void {
    $this->fixed->update(['is_new' => true, 'is_best_seller' => false]);
    $this->regular->update(['is_new' => true, 'is_best_seller' => false]);
    $this->personal->update(['is_new' => false, 'is_best_seller' => false]);
    actingAs($this->customer);

    $both = (string) $this->blade('<x-items.picture3-component :item="$item" />', ['item' => $this->fixed]);
    $newOnly = (string) $this->blade('<x-items.picture3-component :item="$item" />', ['item' => $this->regular]);
    $promoOnly = (string) $this->blade('<x-items.picture3-component :item="$item" />', ['item' => $this->personal]);

    expect($both)->toContain('item-ribbon--new')->toContain('item-ribbon--fixed')
        ->and(mb_substr_count($both, 'class="item-ribbons item-ribbons--2"'))->toBe(1)
        ->and($newOnly)->toContain('item-ribbon--new')->not->toContain('item-ribbon--fixed')
        ->and($promoOnly)->toContain('item-ribbon--personal')->not->toContain('item-ribbon--new')
        ->and($both . $newOnly)->not->toContain('class="if-new"');
});

it('adds best seller to the same ribbon, promotion on its own line when there are three', function (): void {
    $this->fixed->update(['is_new' => true, 'is_best_seller' => true]);
    $this->regular->update(['is_new' => false, 'is_best_seller' => true]);
    actingAs($this->customer);

    $three = (string) $this->blade('<x-items.picture3-component :item="$item" />', ['item' => $this->fixed]);
    $bestOnly = (string) $this->blade('<x-items.picture3-component :item="$item" />', ['item' => $this->regular]);

    expect($three)->toContain('item-ribbon--new')->toContain('item-ribbon--best-seller')->toContain('item-ribbon--fixed')
        ->toContain('item-ribbons item-ribbons--3')
        ->not->toContain('if-best-seller')
        ->and($bestOnly)->toContain('item-ribbon--best-seller')->toContain(e(__('item.badge.best_seller')))
        ->not->toContain('item-ribbon--new')
        ->not->toContain('if-best-seller');
});
