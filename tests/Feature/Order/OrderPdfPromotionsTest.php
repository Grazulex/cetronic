<?php

declare(strict_types=1);

use App\DataObjects\OrderItem\OrderItemDataObject;
use App\Enum\PriceTypeEnum;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\UserBrand;
use Illuminate\Support\Facades\App;

/*
 * On the order PDF the customer must see which lines are on promotion:
 * a fixed price ("prix fixe") or a promo price. The kind is kept on the
 * order line, since the item's prices can change after the order.
 */

beforeEach(function (): void {
    $brand = Brand::factory()->create();
    $category = Category::factory()->create();
    $base = ['brand_id' => $brand->id, 'category_id' => $category->id, 'is_published' => true, 'price' => 20, 'price_b2b' => 20, 'price_promo' => 0, 'price_fix' => 0];

    $this->fixed = Item::factory()->create([...$base, 'reference' => 'FIX-1', 'price_fix' => 8]);
    $this->promo = Item::factory()->create([...$base, 'reference' => 'PROMO-1', 'price_promo' => 12]);
    $this->regular = Item::factory()->create([...$base, 'reference' => 'REG-1']);

    $this->customer = User::factory()->create(['role' => 'customer', 'is_actif' => true, 'language' => 'fr']);
    $this->blocked = User::factory()->create(['role' => 'customer', 'is_actif' => true]);
    foreach ([[$this->customer, false], [$this->blocked, true]] as [$user, $hidePromo]) {
        (new UserBrand([
            'user_id' => $user->id, 'brand_id' => $brand->id, 'category_id' => $category->id,
            'reduction' => 10, 'coefficient' => 0, 'addition_price' => 0, 'price_type' => PriceTypeEnum::DEFAULT,
            'not_show_promo' => $hidePromo, 'is_excluded' => false,
        ]))->saveQuietly();
    }
});

function orderLineFor(User $user, Item $item): array
{
    $order = Order::factory()->create(['user_id' => $user->id]);
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $cartItem = CartItem::factory()->create(['cart_id' => $cart->id, 'item_id' => $item->id]);

    return (new OrderItemDataObject(order: $order, cartItem: $cartItem))->toArray();
}

it('keeps the kind of promotion on the order line', function (): void {
    expect(orderLineFor($this->customer, $this->fixed)['promo_type'])->toBe('fixed')
        ->and(orderLineFor($this->customer, $this->promo)['promo_type'])->toBe('personal')
        ->and(orderLineFor($this->customer, $this->regular)['promo_type'])->toBeNull()
        ->and(orderLineFor($this->blocked, $this->fixed)['promo_type'])->toBeNull();
});

function orderPdf(User $user, array $lines): string
{
    $order = Order::factory()->create(['user_id' => $user->id, 'invoice_country' => 'FRA', 'shipping_country' => 'FRA']);
    foreach ($lines as $line) {
        OrderItem::factory()->create(['order_id' => $order->id, ...$line]);
    }
    App::setLocale('fr');

    return view('pdf/order', ['order' => $order->fresh()])->render();
}

it('strikes the base price and labels a fixed price on the pdf', function (): void {
    $html = orderPdf($this->customer, [
        ['item_id' => $this->fixed->id, 'name' => 'FIX-1', 'price_old' => 20, 'price_show' => 0, 'price_paid' => 8, 'promo_type' => 'fixed'],
    ]);

    expect($html)->toMatch('/class="strike"[^>]*>\s*20,00/')
        ->toContain(__('order.items.promo_type.fixed'));
});

it('strikes the base price and labels a promo price on the pdf', function (): void {
    $html = orderPdf($this->customer, [
        ['item_id' => $this->promo->id, 'name' => 'PROMO-1', 'price_old' => 20, 'price_show' => 12, 'price_paid' => 10.8, 'promo_type' => 'personal'],
    ]);

    expect($html)->toMatch('/class="strike"[^>]*>\s*20,00/')
        ->toContain(__('order.items.promo_type.personal'));
});

it('leaves a regular line as it was', function (): void {
    $html = orderPdf($this->customer, [
        ['item_id' => $this->regular->id, 'name' => 'REG-1', 'price_old' => 20, 'price_show' => 0, 'price_paid' => 18, 'promo_type' => null],
    ]);

    expect($html)->not->toContain('class="strike"')
        ->not->toContain(__('order.items.promo_type.fixed'))
        ->not->toContain(__('order.items.promo_type.personal'));
});
