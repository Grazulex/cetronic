<?php

declare(strict_types=1);

use App\Enum\CartStatusEnum;
use App\Enum\CountryEnum;
use App\Http\Livewire\FormItem;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Location;
use App\Services\CartService;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Livewire\livewire;

it('can be incremented', function (): void {
    $items = createItems(15);
    loginAsUser();

    foreach ($items as $item) {
        livewire(FormItem::class, ['item' => $item, 'quantity' => [$item->multiple_quantity], 'variante' => ['']])
            ->call('up')
            ->assertSee($item->multiple_quantity * 2);
    }
});

it('can be decremented', function (): void {
    $items = createItems(15);
    loginAsUser();

    foreach ($items as $item) {
        livewire(FormItem::class, ['item' => $item, 'quantity' => [$item->multiple_quantity], 'variante' => ['']])
            ->call('up')
            ->assertSee($item->multiple_quantity * 2);

        livewire(FormItem::class, ['item' => $item, 'quantity' => [$item->multiple_quantity], 'variante' => ['']])
            ->call('down')
            ->assertSee($item->multiple_quantity);
    }
});

it('can be added to cart', function (): void {
    $items = createItems(15);
    $user = loginAsUser();
    $totalQuantity = 0;

    foreach ($items as $item) {
        livewire(FormItem::class, ['item' => $item, 'quantity' => [$item->multiple_quantity], 'variante' => ['']])
            ->call('addToCart');

        $totalQuantity += $item->multiple_quantity;
    }
    $cart = Cart::where('user_id', $user->id)->where('status', CartStatusEnum::OPEN->value)->first();
    expect($cart->count())->toBe(1);
    expect(CartItem::where('cart_id', $cart->id)->count())->toBe(15);
    expect(CartItem::where('cart_id', $cart->id)->sum('quantity'))->toBe($totalQuantity);
});

it('can not added to cart if not logged', function (): void {
    $item = createItems(1)->first();

    livewire(FormItem::class, ['item' => $item, 'quantity' => [$item->multiple_quantity], 'variante' => ['']])
        ->call('addToCart')
        ->assertRedirect(route('login'));
});

it('can see cart', function (): void {
    $items = createItems(15);
    $user = loginAsUser();

    foreach ($items as $item) {
        livewire(FormItem::class, ['item' => $item, 'quantity' => [$item->multiple_quantity], 'variante' => ['']])
            ->call('addToCart');
    }
    $cart = Cart::where('user_id', $user->id)->where('status', CartStatusEnum::OPEN->value)->first();
    $cartItem = CartItem::where('cart_id', $cart->id)->get();
    $this->get(uri: route('cart'))
        ->assertStatus(Response::HTTP_OK);
    foreach ($cartItem as $item) {
        expect($this->get(uri: route('cart'))->getContent())->toContain($item->item->name);
    }
});

it('can see VAT if belgium', function (Location $location, CountryEnum $country): void {
    $items = createDefaultItems();
    loginAsUser($location->user);
    foreach ($items as $item) {
        livewire(FormItem::class, ['item' => $item, 'quantity' => [$item->multiple_quantity], 'variante' => ['']])
            ->call('addToCart');
    }
    $cart = Cart::where('user_id', $location->user->id)->where('status', CartStatusEnum::OPEN->value)->first();
    expect($location->country)->toBe($country);

    $cartService = new CartService();
    $vat = $cartService->getVAT($cart);
    $shippingVat = $cartService->getShippingVAT();
    $total = $vat + $shippingVat;
    $this->get(uri: route('cart'))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee(__('cart.vat'))
        ->assertSee(str_replace('.', ',', (string)$total))
    ;
})->with(data: 'locations_be');

it('can not see VAT if not belgium', function (Location $location, CountryEnum $country): void {
    $items = createDefaultItems();
    loginAsUser($location->user);
    foreach ($items as $item) {
        livewire(FormItem::class, ['item' => $item, 'quantity' => [$item->multiple_quantity], 'variante' => ['']])
            ->call('addToCart');
    }
    $cart = Cart::where('user_id', $location->user->id)->where('status', CartStatusEnum::OPEN->value)->first();
    expect($location->country)->toBe($country);
    $cartService = new CartService();
    $vat = $cartService->getVAT($cart);
    $shippingVat = $cartService->getShippingVAT();
    $total = $vat + $shippingVat;

    $this->get(uri: route('cart'))
        ->assertStatus(Response::HTTP_OK)
        ->assertDontSee(__('cart.vat'))
        ->assertDontSee(str_replace('.', ',', (string)$total));
})->with(data: 'locations_fr');

it('have free shipping if above franco', function (): void {
})->todo();

it('have shipping if under franco', function (): void {
})->todo();

it('have shipping if fixe shipping price', function (): void {
})->todo();

it('can update quantity', function (): void {
})->todo();
