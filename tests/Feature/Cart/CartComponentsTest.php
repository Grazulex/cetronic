<?php

declare(strict_types=1);

use App\Enum\LocationTypeEnum;
use App\Http\Livewire\CartCounter;
use App\Http\Livewire\DeleteCart;
use App\Http\Livewire\FormCart;
use App\Http\Livewire\LocationCart;
use App\Http\Livewire\PriceTotalCart;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Location;
use App\Models\User;

use function Pest\Livewire\livewire;

/*
 * Front cart components. These pin the events each component sends
 * (cart_updated, quantity_updated, total_updated), which are the contract
 * between components: emit() becomes dispatch() in Livewire 3, and the
 * assertEmitted() calls below become assertDispatched().
 */

function cartLine(User $user, int $quantity = 2, int $multiple = 1): CartItem
{
    $item = createItems(1)->first();
    $item->update(['multiple_quantity' => $multiple]);
    $cart = Cart::factory()->create(['user_id' => $user->id]);

    return CartItem::factory()->create([
        'cart_id' => $cart->id,
        'item_id' => $item->id,
        'quantity' => $quantity,
        'price' => 10,
        'price_promo' => 0,
    ]);
}

it('counts the quantities of the open cart', function (): void {
    $line = cartLine(loginAsUser(), quantity: 3);
    CartItem::factory()->create(['cart_id' => $line->cart_id, 'item_id' => $line->item_id, 'quantity' => 4]);

    livewire(CartCounter::class)->assertSeeHtml('id="panier_not">7</div>');
});

it('shows no counter for a guest', function (): void {
    livewire(CartCounter::class)->assertDontSeeHtml('panier_not');
});

it('refreshes the counter when the cart changes', function (): void {
    $line = cartLine(loginAsUser(), quantity: 3);
    $counter = livewire(CartCounter::class)->assertSeeHtml('>3</div>');

    $line->update(['quantity' => 5]);

    $counter->emit('cart_updated')->assertSeeHtml('>5</div>');
});

it('updates the quantity of a cart line', function (): void {
    $line = cartLine(loginAsUser(), quantity: 2);

    livewire(FormCart::class, ['cartItem' => $line])
        ->set("quantity.{$line->id}", 6)
        ->call('updateCart')
        ->assertEmitted('cart_updated')
        ->assertEmitted('quantity_updated')
        ->assertEmitted('total_updated');

    expect($line->fresh()->quantity)->toBe(6);
});

it('rounds a quantity that is not a multiple of the item pack size', function (): void {
    $line = cartLine(loginAsUser(), quantity: 5, multiple: 4);

    livewire(FormCart::class, ['cartItem' => $line]);

    expect($line->fresh()->quantity)->toBe(4);
});

it('removes a line and stays on the cart when other lines remain', function (): void {
    $line = cartLine(loginAsUser());
    $other = CartItem::factory()->create(['cart_id' => $line->cart_id, 'item_id' => $line->item_id]);

    livewire(DeleteCart::class, ['cartItem' => $line])
        ->call('removeItem')
        ->assertRedirect(route('cart'));

    expect(CartItem::find($line->id))->toBeNull()
        ->and(CartItem::find($other->id))->not->toBeNull();
});

it('deletes the cart and goes home when the last line is removed', function (): void {
    $line = cartLine(loginAsUser());

    livewire(DeleteCart::class, ['cartItem' => $line])
        ->call('removeItem')
        ->assertRedirect(route('home'));

    expect(Cart::find($line->cart_id))->toBeNull();
});

it('saves the invoice and shipping addresses of the cart', function (): void {
    $user = loginAsUser();
    $line = cartLine($user);
    $invoice = Location::factory()->create(['user_id' => $user->id, 'type' => LocationTypeEnum::INVOICE->value]);
    $shipping = Location::factory()->create(['user_id' => $user->id, 'type' => LocationTypeEnum::SHIPPING->value]);

    livewire(LocationCart::class, ['cart' => $line->cart])
        ->set('locationInvoice', $invoice->id)
        ->set('locationShipping', $shipping->id)
        ->assertEmitted('total_updated');

    expect($line->cart->fresh())
        ->invoice_location_id->toBe($invoice->id)
        ->shipping_location_id->toBe($shipping->id);
});

it('saves the cart comment and rejects a too long one', function (): void {
    $line = cartLine(loginAsUser());

    livewire(LocationCart::class, ['cart' => $line->cart])
        ->set('comment', 'Livrer le matin')
        ->assertHasNoErrors()
        ->set('comment', str_repeat('x', 256))
        ->assertHasErrors(['comment' => 'max']);

    expect($line->cart->fresh()->comment)->toBe('Livrer le matin');
});

it('shows the line total', function (): void {
    $line = cartLine(loginAsUser(), quantity: 3);

    livewire(PriceTotalCart::class, ['cartItem' => $line])->assertSee('30,00€');
});
