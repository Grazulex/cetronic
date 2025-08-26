<?php

declare(strict_types=1);

use App\Enum\PriceTypeEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\Item;
use App\Models\ItemMeta;
use App\Models\UserBrand;
use App\Services\ItemService;

function createItemsWithPriceFix(): \Illuminate\Support\Collection
{
    Brand::factory()->count(1)->create(['is_featured' => true]);
    $category = Category::factory()->count(1)->create();
    $categoryMeta = CategoryMeta::factory()->count(1)->create(['category_id' => $category->first()->id]);
    
    $items = [];
    for ($i = 1; $i <= 5; $i++) {
        $price = $i * 10;
        $items[] = Item::factory()->create([
            'brand_id' => Brand::first()->id, 
            'category_id' => $category->first()->id, 
            'is_published' => true, 
            'price' => $price, 
            'price_b2b' => $price - 1, 
            'price_promo' => $price / 2, 
            'price_special1' => $price - 2, 
            'price_special2' => $price - 3, 
            'price_special3' => $price - 4, 
            'price_fix' => $price + 5, // Prix fixe supérieur au prix normal
            'sale_price' => $price - 5
        ]);
    }

    foreach ($items as $item) {
        ItemMeta::factory()->count(1)->create([
            'item_id' => $item->id, 
            'meta_id' => $categoryMeta->first()->id, 
            'value' => 'test'
        ]);
    }

    return collect($items);
}

it('should use price_fix as price_end when user has rule with not_show_promo false', function (): void {
    $items = createItemsWithPriceFix();
    $user = loginAsUser();
    
    UserBrand::factory()->create([
        'user_id' => $user->id,
        'brand_id' => $items->first()->brand->id,
        'category_id' => $items->first()->category->id,
        'is_excluded' => false,
        'price_type' => PriceTypeEnum::DEFAULT,
        'not_show_promo' => false, // Promotions autorisées
        'reduction' => 0,
        'coefficient' => 0,
        'addition_price' => 0,
    ]);

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice($user);
        
        // Le price_fix devrait être utilisé comme price_end
        expect((int) ($prices['price_end'] * 100))->toBe((int) ($item->price_fix * 100));
        expect((int) ($prices['price_start'] * 100))->toBe((int) ($item->price * 100));
        expect((int) ($prices['price_promo'] * 100))->toBe((int) ($item->price_promo * 100));
    }
});

it('should NOT use price_fix as price_end when user has rule with not_show_promo true', function (): void {
    $items = createItemsWithPriceFix();
    $user = loginAsUser();
    
    UserBrand::factory()->create([
        'user_id' => $user->id,
        'brand_id' => $items->first()->brand->id,
        'category_id' => $items->first()->category->id,
        'is_excluded' => false,
        'price_type' => PriceTypeEnum::DEFAULT,
        'not_show_promo' => true, // Promotions masquées
        'reduction' => 0,
        'coefficient' => 0,
        'addition_price' => 0,
    ]);

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice($user);
        
        // Le price_fix ne devrait PAS être utilisé car not_show_promo = true
        expect((int) ($prices['price_end'] * 100))->toBe((int) ($item->price * 100));
        expect((int) ($prices['price_start'] * 100))->toBe((int) ($item->price * 100));
        expect((int) ($prices['price_promo'] * 100))->toBe(0); // Pas de promo car masquée
    }
});

it('should use price_fix as price_end when user has rule with price_type FIX and not_show_promo false', function (): void {
    $items = createItemsWithPriceFix();
    $user = loginAsUser();
    
    UserBrand::factory()->create([
        'user_id' => $user->id,
        'brand_id' => $items->first()->brand->id,
        'category_id' => $items->first()->category->id,
        'is_excluded' => false,
        'price_type' => PriceTypeEnum::FIX, // Type de prix FIX
        'not_show_promo' => false,
        'reduction' => 10, // Cette réduction ne devrait pas s'appliquer
        'coefficient' => 2, // Ce coefficient ne devrait pas s'appliquer
        'addition_price' => 5, // Cette addition ne devrait pas s'appliquer
    ]);

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice($user);
        
        // Le price_fix de l'item devrait être utilisé ET être prioritaire
        expect((int) ($prices['price_end'] * 100))->toBe((int) ($item->price_fix * 100));
        expect((int) ($prices['price_start'] * 100))->toBe((int) ($item->price_fix * 100)); // price_start est aussi price_fix
        expect((int) ($prices['price_promo'] * 100))->toBe((int) ($item->price_promo * 100));
    }
});

it('should use price_fix as price_end when user has NO rule', function (): void {
    $items = createItemsWithPriceFix();
    $user = loginAsUser();
    
    // Pas de règle UserBrand créée

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice($user);
        
        // Le price_fix devrait être utilisé comme price_end quand pas de règle
        expect((int) ($prices['price_end'] * 100))->toBe((int) ($item->price_fix * 100));
        expect((int) ($prices['price_start'] * 100))->toBe((int) ($item->price * 100));
        expect((int) ($prices['price_promo'] * 100))->toBe((int) ($item->price_promo * 100));
    }
});

it('should use price_fix as price_end for guests', function (): void {
    $items = createItemsWithPriceFix();
    
    // Pas d'utilisateur connecté (guest)

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice(); // Pas d'utilisateur
        
        // Le price_fix devrait être utilisé comme price_end pour les invités
        expect((int) ($prices['price_end'] * 100))->toBe((int) ($item->price_fix * 100));
        expect((int) ($prices['price_start'] * 100))->toBe((int) ($item->price * 100));
        expect((int) ($prices['price_promo'] * 100))->toBe((int) ($item->price_promo * 100));
        expect((int) ($prices['sale'] * 100))->toBe(0); // Sale toujours 0 pour guests
    }
});

it('should apply reduction to price_fix when price_type is DEFAULT and not_show_promo false with reduction', function (): void {
    $items = createItemsWithPriceFix();
    $user = loginAsUser();
    
    UserBrand::factory()->create([
        'user_id' => $user->id,
        'brand_id' => $items->first()->brand->id,
        'category_id' => $items->first()->category->id,
        'is_excluded' => false,
        'price_type' => PriceTypeEnum::DEFAULT,
        'not_show_promo' => false,
        'reduction' => 20, // 20% de réduction
        'coefficient' => 0,
        'addition_price' => 0,
    ]);

    foreach ($items as $item) {
        $itemService = new ItemService($item);
        $prices = $itemService->getPrice($user);
        
        // Le price_fix devrait toujours avoir la priorité, même avec réduction
        expect((int) ($prices['price_end'] * 100))->toBe((int) ($item->price_fix * 100));
        
        // Vérifier que la réduction est calculée mais écrasée par price_fix
        $expectedReduction = $item->price_promo - ($item->price_promo * 0.2);
        expect($expectedReduction)->toBeLessThan($prices['price_end']); // price_fix > prix avec réduction
    }
});

it('should not use price_fix when item has no price_fix set', function (): void {
    Brand::factory()->count(1)->create(['is_featured' => true]);
    $category = Category::factory()->count(1)->create();
    $categoryMeta = CategoryMeta::factory()->count(1)->create(['category_id' => $category->first()->id]);
    
    // Item SANS price_fix
    $item = Item::factory()->create([
        'brand_id' => Brand::first()->id, 
        'category_id' => $category->first()->id, 
        'is_published' => true, 
        'price' => 100, 
        'price_b2b' => 99, 
        'price_promo' => 50, 
        'price_fix' => 0, // PAS de prix fixe
        'sale_price' => 95
    ]);
    
    ItemMeta::factory()->create([
        'item_id' => $item->id, 
        'meta_id' => $categoryMeta->first()->id, 
        'value' => 'test'
    ]);

    $user = loginAsUser();
    
    UserBrand::factory()->create([
        'user_id' => $user->id,
        'brand_id' => $item->brand->id,
        'category_id' => $item->category->id,
        'is_excluded' => false,
        'price_type' => PriceTypeEnum::DEFAULT,
        'not_show_promo' => false,
        'reduction' => 0,
        'coefficient' => 0,
        'addition_price' => 0,
    ]);

    $itemService = new ItemService($item);
    $prices = $itemService->getPrice($user);
    
    // Comportement normal sans price_fix
    expect((int) ($prices['price_end'] * 100))->toBe((int) ($item->price_promo * 100));
    expect((int) ($prices['price_start'] * 100))->toBe((int) ($item->price * 100));
    expect((int) ($prices['price_promo'] * 100))->toBe((int) ($item->price_promo * 100));
});
