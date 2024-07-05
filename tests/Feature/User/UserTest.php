<?php

declare(strict_types=1);

use App\Enum\PriceTypeEnum;
use App\Models\Item;
use App\Models\UserBrand;
use App\Services\ItemService;

it('have access all the items', function (): void {
    $items = createItems(15);
    $user = loginAsUser();
    UserBrand::factory()->count(1)->create(['user_id' => $user->id, 'brand_id' => $items->first()->brand->id, 'category_id' => $items->first()->category->id, 'is_excluded' => false]);
    $itemsShow = Item::where('brand_id', $items->first()->brand->id)
        ->where('category_id', $items->first()->category->id)
        ->active()
        ->whereNotIn('id', function ($query) use ($user): void {
            $query->select('item_id')
                ->from('user_disables')
                ->where('user_id', $user->id)
                ->where('is_enable', true);
        })->get();

    expect($itemsShow)->toHaveCount(15);
});

it('have not access all the items', function (): void {
    $items = createItems(15);
    $user = loginAsUser();
    UserBrand::factory()->count(1)->create(['user_id' => $user->id, 'brand_id' => $items->first()->brand->id, 'category_id' => $items->first()->category->id, 'is_excluded' => true]);

    $itemsShow = Item::where('brand_id', $items->first()->brand->id)
        ->where('category_id', $items->first()->category->id)
        ->active()
        ->whereNotIn('id', function ($query) use ($user): void {
            $query->select('item_id')
                ->from('user_disables')
                ->where('user_id', $user->id)
                ->where('is_enable', true);
        })->get();

    expect($itemsShow)->toHaveCount(0);
});

it('have reduction without promo with default price', closure: function (): void {
    $items = createDefaultItems();
    $user = loginAsUser();
    $userBrand = UserBrand::factory()->count(1)->create(['user_id' => $user->id, 'brand_id' => $items->first()->brand->id, 'category_id' => $items->first()->category->id, 'is_excluded' => false, 'reduction' => 50, 'coefficient' => 0, 'addition_price' => 0, 'price_type' => PriceTypeEnum::DEFAULT, 'not_show_promo' => true]);

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice($user);
        expect((int) $prices['price_start'] * 100)->toBe($item->price * 100);
        expect((int) $prices['price_promo'] * 100)->toBe(0);
        expect((int) $prices['sale'] * 100)->toBe($item->sale_price * 100);
        expect((int) $prices['price_end'] * 100)->toBe((int) ($item->price - ($item->price * $userBrand->first()->reduction / 100)) * 100);
    }
});

it('have coefficient without promo with default price', closure: function (): void {
    $items = createDefaultItems();
    $user = loginAsUser();
    $userBrand = UserBrand::factory()->count(1)->create(['user_id' => $user->id, 'brand_id' => $items->first()->brand->id, 'category_id' => $items->first()->category->id, 'is_excluded' => false,'reduction' => 0, 'coefficient' => 2, 'addition_price' => 0, 'price_type' => PriceTypeEnum::DEFAULT, 'not_show_promo' => true]);

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice($user);
        expect((int) $prices['price_start'] * 100)->toBe(($item->price * $userBrand->first()->coefficient) * 100);
        expect((int) $prices['price_promo'] * 100)->toBe(0);
        expect((int) $prices['sale'] * 100)->toBe($item->sale_price * 100);
        expect((int) $prices['price_end'] * 100)->toBe(($item->price * $userBrand->first()->coefficient) * 100);
    }
});

it('have additional price without promo with default price', closure: function (): void {
    $items = createDefaultItems();
    $user = loginAsUser();
    $userBrand = UserBrand::factory()->count(1)->create(['user_id' => $user->id, 'brand_id' => $items->first()->brand->id, 'category_id' => $items->first()->category->id, 'is_excluded' => false, 'reduction' => 0, 'coefficient' => 0, 'addition_price' => 10, 'price_type' => PriceTypeEnum::DEFAULT, 'not_show_promo' => true]);

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice($user);
        expect((int) $prices['price_start'] * 100)->toBe(($item->price + $userBrand->first()->addition_price) * 100);
        expect((int) $prices['price_promo'] * 100)->toBe(0);
        expect((int) $prices['sale'] * 100)->toBe($item->sale_price * 100);
        expect((int) $prices['price_end'] * 100)->toBe(($item->price + $userBrand->first()->addition_price) * 100);
    }
});

it('have promo with default price', closure: function (): void {
    $items = createDefaultItems();
    $user = loginAsUser();
    UserBrand::factory()->count(1)->create(['user_id' => $user->id, 'brand_id' => $items->first()->brand->id, 'category_id' => $items->first()->category->id, 'is_excluded' => false, 'reduction' => 0, 'coefficient' => 0, 'addition_price' => 0, 'price_type' => PriceTypeEnum::DEFAULT, 'not_show_promo' => false]);

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice($user);
        expect((int) $prices['price_start'] * 100)->toBe((int) $item->price * 100);
        expect((int) $prices['price_promo'] * 100)->toBe((int) $item->price_promo * 100);
        expect((int) $prices['sale'] * 100)->toBe((int) $item->sale_price * 100);
        expect((int) $prices['price_end'] * 100)->toBe((int) (($item->price_promo > 0) ? $item->price_promo : $item->price) * 100);
    }
});

it('have reduction with promo with default price', closure: function (): void {
    $items = createDefaultItems();
    $user = loginAsUser();
    $userBrand = UserBrand::factory()->count(1)->create(['user_id' => $user->id, 'brand_id' => $items->first()->brand->id, 'category_id' => $items->first()->category->id, 'is_excluded' => false, 'reduction' => 50, 'coefficient' => 0, 'addition_price' => 0, 'price_type' => PriceTypeEnum::DEFAULT, 'not_show_promo' => false]);

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice($user);
        expect((int) $prices['price_start'] * 100)->toBe((int) $item->price * 100);
        expect((int) $prices['price_promo'] * 100)->toBe((int) $item->price_promo * 100);
        expect((int) $prices['sale'] * 100)->toBe((int) $item->sale_price * 100);
        expect((int) $prices['price_end'] * 100)->toBe((int) ((($item->price_promo > 0) ? $item->price_promo : $item->price) - ((($item->price_promo > 0) ? $item->price_promo : $item->price) * $userBrand->first()->reduction / 100)) * 100);
    }
});


it('have coefficient with promo with default price', closure: function (): void {
    $items = createDefaultItems();
    $user = loginAsUser();
    $userBrand = UserBrand::factory()->count(1)->create(['user_id' => $user->id, 'brand_id' => $items->first()->brand->id, 'category_id' => $items->first()->category->id, 'is_excluded' => false,'reduction' => 0, 'coefficient' => 2, 'addition_price' => 0, 'price_type' => PriceTypeEnum::DEFAULT, 'not_show_promo' => false]);

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice($user);
        expect((int) $prices['price_start'] * 100)->toBe((int) ($item->price * $userBrand->first()->coefficient) * 100);
        expect((int) $prices['price_promo'] * 100)->toBe((int) ($item->price_promo * $userBrand->first()->coefficient) * 100);
        expect((int) $prices['sale'] * 100)->toBe((int) $item->sale_price * 100);
        expect((int) $prices['price_end'] * 100)->toBe((int) ($item->price_promo * $userBrand->first()->coefficient) * 100);
    }
});


it('have additional price with promo with default price', closure: function (): void {
    $items = createDefaultItems();
    $user = loginAsUser();
    $userBrand = UserBrand::factory()->count(1)->create(['user_id' => $user->id, 'brand_id' => $items->first()->brand->id, 'category_id' => $items->first()->category->id, 'is_excluded' => false, 'reduction' => 0, 'coefficient' => 0, 'addition_price' => 10, 'price_type' => PriceTypeEnum::DEFAULT, 'not_show_promo' => false]);

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice($user);
        expect((int) $prices['price_start'] * 100)->toBe((int) ($item->price + $userBrand->first()->addition_price) * 100);
        expect((int) $prices['price_promo'] * 100)->toBe((int) ($item->price_promo + $userBrand->first()->addition_price) * 100);
        expect((int) $prices['sale'] * 100)->toBe((int) $item->sale_price * 100);
        expect((int) $prices['price_end'] * 100)->toBe((int) ($item->price_promo + $userBrand->first()->addition_price) * 100);
    }
});

it('have reduction + coefficient without promo with promo price', closure: function (): void {
    $items = createDefaultItems();
    $user = loginAsUser();
    $userBrand = UserBrand::factory()->count(1)->create(['user_id' => $user->id, 'brand_id' => $items->first()->brand->id, 'category_id' => $items->first()->category->id, 'is_excluded' => false, 'reduction' => 50, 'coefficient' => 2, 'addition_price' => 0, 'price_type' => PriceTypeEnum::DEFAULT, 'not_show_promo' => true]);

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice($user);
        expect((int) $prices['price_start'] * 100)->toBe($item->price * $userBrand->first()->coefficient * 100);
        expect((int) $prices['price_promo'] * 100)->toBe(0);
        expect((int) $prices['sale'] * 100)->toBe($item->sale_price * 100);
        expect((int) $prices['price_end'] * 100)->toBe((($item->price * $userBrand->first()->coefficient) - (($item->price * $userBrand->first()->coefficient) * $userBrand->first()->reduction / 100)) * 100);
    }
});

it('have reduction + coefficient + additional price without promo with promo price', closure: function (): void {
    $items = createDefaultItems();
    $user = loginAsUser();
    $userBrand = UserBrand::factory()->count(1)->create(['user_id' => $user->id, 'brand_id' => $items->first()->brand->id, 'category_id' => $items->first()->category->id, 'is_excluded' => false, 'reduction' => 50, 'coefficient' => 2, 'addition_price' => 10, 'price_type' => PriceTypeEnum::DEFAULT, 'not_show_promo' => true]);

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice($user);
        expect((int) $prices['price_start'] * 100)->toBe(($item->price + $userBrand->first()->addition_price) * $userBrand->first()->coefficient * 100);
        expect((int) $prices['price_promo'] * 100)->toBe(0);
        expect((int) $prices['sale'] * 100)->toBe($item->sale_price * 100);
        expect((int) $prices['price_end'] * 100)->toBe(((($item->price + $userBrand->first()->addition_price) * $userBrand->first()->coefficient) - ((($item->price +  $userBrand->first()->addition_price) * $userBrand->first()->coefficient) * $userBrand->first()->reduction / 100)) * 100);
    }
});

it('have reduction + coefficient with promo with promo price', closure: function (): void {
    $items = createDefaultItems();
    $user = loginAsUser();
    $userBrand = UserBrand::factory()->count(1)->create(['user_id' => $user->id, 'brand_id' => $items->first()->brand->id, 'category_id' => $items->first()->category->id, 'is_excluded' => false, 'reduction' => 50, 'coefficient' => 2, 'addition_price' => 0, 'price_type' => PriceTypeEnum::DEFAULT, 'not_show_promo' => false]);

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice($user);
        expect((int) $prices['price_start'] * 100)->toBe($item->price * $userBrand->first()->coefficient * 100);
        expect((int) $prices['price_promo'] * 100)->toBe($item->price_promo * $userBrand->first()->coefficient * 100);
        expect((int) $prices['sale'] * 100)->toBe($item->sale_price * 100);
        expect((int) $prices['price_end'] * 100)->toBe((($item->price_promo * $userBrand->first()->coefficient) - (($item->price_promo * $userBrand->first()->coefficient) * $userBrand->first()->reduction / 100)) * 100);
    }
});

it('have reduction + coefficient + additional price with promo with promo price', closure: function (): void {
    $items = createDefaultItems();
    $user = loginAsUser();
    $userBrand = UserBrand::factory()->count(1)->create(['user_id' => $user->id, 'brand_id' => $items->first()->brand->id, 'category_id' => $items->first()->category->id, 'is_excluded' => false, 'reduction' => 50, 'coefficient' => 2, 'addition_price' => 10, 'price_type' => PriceTypeEnum::DEFAULT, 'not_show_promo' => false]);

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice($user);
        expect((int) $prices['price_start'] * 100)->toBe(($item->price + $userBrand->first()->addition_price) * $userBrand->first()->coefficient * 100);
        expect((int) $prices['price_promo'] * 100)->toBe(($item->price_promo + $userBrand->first()->addition_price) * $userBrand->first()->coefficient * 100);
        expect((int) $prices['sale'] * 100)->toBe($item->sale_price * 100);
        expect((int) $prices['price_end'] * 100)->toBe(((($item->price_promo + $userBrand->first()->addition_price) * $userBrand->first()->coefficient) - ((($item->price_promo + $userBrand->first()->addition_price) * $userBrand->first()->coefficient) * $userBrand->first()->reduction / 100)) * 100);
    }
});
