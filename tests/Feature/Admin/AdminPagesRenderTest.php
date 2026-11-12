<?php

declare(strict_types=1);

use App\Enum\UserRoleEnum;
use App\Filament\Pages\ImportBrandDiscounts;
use App\Filament\Pages\ImportCSV;
use App\Filament\Pages\ImportPictures;
use App\Filament\Pages\SalesStats;
use App\Filament\Resources\BrandResource;
use App\Filament\Resources\CartResource;
use App\Filament\Resources\CategoryMetaResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CustomerTypeResource;
use App\Filament\Resources\ItemPublishLogResource;
use App\Filament\Resources\ItemResource;
use App\Filament\Resources\MessageResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\PdfCatalogResource;
use App\Filament\Resources\PubResource;
use App\Filament\Resources\UserResource;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\CustomerType;
use App\Models\Message;
use App\Models\Order;
use App\Models\Pub;
use App\Models\User;

/*
 * Smoke tests for the Filament admin: every page must render for an admin.
 * URLs are built with getUrl() so the tests survive the Filament v2 -> v3
 * migration, where route names change but getUrl() keeps working.
 */

beforeEach(function (): void {
    loginAsAdmin();
});

it('renders the resource list and create pages', function (string $url): void {
    $this->get($url)->assertOk();
})->with([
    'brands index' => fn() => BrandResource::getUrl('index'),
    'brands create' => fn() => BrandResource::getUrl('create'),
    'carts index' => fn() => CartResource::getUrl('index'),
    'carts create' => fn() => CartResource::getUrl('create'),
    'category metas index' => fn() => CategoryMetaResource::getUrl('index'),
    'categories index' => fn() => CategoryResource::getUrl('index'),
    'categories create' => fn() => CategoryResource::getUrl('create'),
    'customer types index' => fn() => CustomerTypeResource::getUrl('index'),
    'customer types create' => fn() => CustomerTypeResource::getUrl('create'),
    'item publish logs index' => fn() => ItemPublishLogResource::getUrl('index'),
    'items index' => fn() => ItemResource::getUrl('index'),
    'items create' => fn() => ItemResource::getUrl('create'),
    'messages index' => fn() => MessageResource::getUrl('index'),
    'messages create' => fn() => MessageResource::getUrl('create'),
    'orders index' => fn() => OrderResource::getUrl('index'),
    'orders create' => fn() => OrderResource::getUrl('create'),
    'pdf catalogs index' => fn() => PdfCatalogResource::getUrl('index'),
    'pdf catalogs create' => fn() => PdfCatalogResource::getUrl('create'),
    'pdf catalogs regenerate' => fn() => PdfCatalogResource::getUrl('regenerate'),
    'pubs index' => fn() => PubResource::getUrl('index'),
    'pubs create' => fn() => PubResource::getUrl('create'),
    'users index' => fn() => UserResource::getUrl('index'),
    'users create' => fn() => UserResource::getUrl('create'),
]);

it('renders the resource edit pages with their relation managers', function (string $url): void {
    $this->get($url)->assertOk();
})->with([
    'brand' => fn() => BrandResource::getUrl('edit', ['record' => Brand::factory()->create()]),
    'cart' => fn() => CartResource::getUrl('edit', ['record' => Cart::factory()->create(['user_id' => User::factory()->create()->id])]),
    'category meta' => fn() => CategoryMetaResource::getUrl('edit', ['record' => CategoryMeta::factory()->create(['category_id' => Category::factory()->create()->id])]),
    'category' => fn() => CategoryResource::getUrl('edit', ['record' => Category::factory()->create()]),
    'customer type' => fn() => CustomerTypeResource::getUrl('edit', ['record' => CustomerType::create(['name' => 'Pro', 'is_active' => true])]),
    'item' => fn() => ItemResource::getUrl('edit', ['record' => createItems(1)->first()]),
    'message' => fn() => MessageResource::getUrl('edit', ['record' => Message::factory()->create()]),
    'order' => fn() => OrderResource::getUrl('edit', ['record' => Order::factory()->create()]),
    'pub' => fn() => PubResource::getUrl('edit', ['record' => Pub::factory()->create()]),
    'user' => fn() => UserResource::getUrl('edit', ['record' => User::factory()->create()]),
    'customer stats' => fn() => UserResource::getUrl('customer-stats', ['record' => User::factory()->create(['role' => UserRoleEnum::CUSTOMER])]),
]);

it('renders the custom admin pages', function (string $url): void {
    $this->get($url)->assertOk();
})->with([
    'import brand discounts' => fn() => ImportBrandDiscounts::getUrl(),
    'import csv' => fn() => ImportCSV::getUrl(),
    'import pictures' => fn() => ImportPictures::getUrl(),
    'sales stats' => fn() => SalesStats::getUrl(),
]);
