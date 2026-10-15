<?php

declare(strict_types=1);

use App\Services\BrandService;

it('should not expose promo categories to guests', function (): void {
    createItemsWithPriceFix();

    expect((new BrandService())->getCategoriesWithPromos(null))->toBe([]);
});
