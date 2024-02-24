<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Cart\CreateCartAction;
use App\Actions\CartItem\CreateCartItemAction;
use App\DataObjects\Cart\CartDataObject;
use App\DataObjects\CartItem\CartItemDataObject;
use App\Enum\CountryEnum;
use App\Enum\LocationTypeEnum;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Collection;

final class CartService
{
    public function getCartContent(Cart $cart): Collection
    {
        return CartItem::where('cart_id', $cart->id)
            ->with('item', function ($query): void {
                $query->with('brand', function ($query): void {
                    $query->orderBy('name');
                })
                    ->orderBy('reference');
            })
            ->get()
            ->sortBy('item.brand.name')
            ->sortBy('item.reference');
    }

    public function getCartCount(Cart $cart): int
    {
        return (int) CartItem::where('cart_id', $cart->id)->sum('quantity');
    }

    public function creatNewCart(User $user, string $cookie = null): Cart
    {
        if ($cookie) {
            $cart = (new CreateCartAction())->handle(
                new CartDataObject(
                    user: (new GuestService())->getGuestUser(),
                    cookie: $cookie,
                )
            );
        } else {
            $cart = (new CreateCartAction())->handle(
                new CartDataObject(
                    user: $user,
                    shipping_location: ($shipping = $user->locations()->where('type', LocationTypeEnum::SHIPPING)->first()) ? $shipping : null,
                    invoice_location: ($invoice = $user->locations()->where('type', LocationTypeEnum::INVOICE)->first()) ? $invoice : null,
                )
            );
        }

        return $cart;
    }

    public function deleteCart(Cart $cart): void
    {
        CartItem::where('cart_id', $cart->id)->delete();
        $cart->delete();

        if ( ! auth()->check()) {
            $guestService = new GuestService();
            $guestService->forgetCookie('cetronic_cart');
        }
    }

    public function updatePrices(Cart $cart, Item $item, float $price, float $price_promo = 0, float $price_old = 0, string $variant = ''): CartItem
    {
        $cartItem = CartItem::where('item_id', $item->id)
            ->where('cart_id', $cart->id)
            ->where('variant', $variant)
            ->first();
        $cartItem?->update([
            'price' => $price,
            'price_old' => $price_old,
            'price_promo' => $price_promo,
        ]);

        return $cartItem;
    }

    public function addToCart(Cart $cart, Item $item, float $price, float $price_promo = 0, int $quantity = 1, float $price_old = 0, string $variant = ''): CartItem
    {
        $cartItem = CartItem::where('item_id', $item->id)
            ->where('cart_id', $cart->id)
            ->where('variant', $variant)
            ->first();
        if ($cartItem) {
            $cartItem->update([
                'quantity' => $cartItem->quantity + $quantity,
            ]);
        } else {
            $cartItem = (new CreateCartItemAction())->handle(
                new CartItemDataObject(
                    item: $item,
                    cart: $cart,
                    price: $price,
                    price_old: $price_old,
                    price_promo: $price_promo,
                    quantity: $quantity,
                    variant: $variant,
                )
            );
        }

        return $cartItem;
    }

    public function updateCart(CartItem $cartItem, int $quantity): CartItem
    {
        $cartItem->update([
            'quantity' => $quantity,
        ]);

        return $cartItem;
    }

    public function removeItem(CartItem $cartItem): void
    {
        $cartItem->delete();
    }

    public function getShippingTotalVAT(): float
    {
        $shipping = $this->getShippingTotal();
        $vat = $this->getShippingVAT();

        return $shipping + $vat;
    }

    public function getShippingTotal(): int
    {
        $shipping = 0;
        if (auth()->check()) {
            $userService = new UserService(auth()->user());
            $addShippingPrice = $userService->getFixedShippingPrice();
            $franco = $userService->getFranco();
            $shipping += $addShippingPrice;
            if ($franco > 0) {
                if ($shipping >= $franco) {
                    $shipping = 0;
                }
            }
        }

        return $shipping;
    }

    public function getShippingVAT(): float
    {
        $shipping = $this->getShippingTotal();

        return $shipping * 21 / 100;
    }

    public function getTotalVAT(Cart $cart): float
    {
        $total = $this->getTotal(cart: $cart);
        $vat = $this->getVAT(cart: $cart);
        $discount = $this->getDiscount(cart: $cart);

        return $total - $discount + $vat;
    }

    public function getTotal(Cart $cart): float
    {
        $total = 0;
        $items = $cart->items;
        foreach ($items as $item) {
            if ($item->price_promo > 0) {
                $total += $item->price_promo * $item->quantity;
            } else {
                $total += $item->price * $item->quantity;
            }
        }

        return $total;
    }

    public function getVAT(Cart $cart): float
    {
        $vat = 0;
        $price_promo = 0;
        $price = 0;
        $items = $cart->items;
        foreach ($items as $item) {
            if (auth()->check()) {
                $discount = (new UserService(auth()->user()))->getBrandAndCategoryDiscounts($item->item->brand, $item->item->category, $item->quantity);
                if ($discount) {
                    $price_promo = $item->price_promo - ($item->price_promo * ($discount->reduction / 100));
                    $price = $item->price - ($item->price * ($discount->reduction / 100));
                }
            }

            if ($item->price_promo > 0) {
                $vat += (($price_promo ?: $item->price_promo) * 21 / 100) * $item->quantity;
            } else {
                $vat += (($price ?: $item->price) * 21 / 100) * $item->quantity;
            }
        }

        return $vat;
    }

    public function getDiscount(Cart $cart): float
    {
        $totalDiscount = 0;
        $items = $cart->items;
        foreach ($items as $item) {
            if (auth()->check()) {
                $discount = (new UserService(auth()->user()))->getBrandAndCategoryDiscounts($item->item->brand, $item->item->category, $item->quantity);
                if ($discount) {
                    if ($item->price_promo) {
                        $totalDiscount += $item->price_promo * $item->quantity * ($discount->reduction / 100);
                    } else {
                        $totalDiscount += $item->price * $item->quantity * ($discount->reduction / 100);
                    }
                }
            }
        }

        return $totalDiscount;
    }

    public function needVAT(Cart $cart): bool
    {
        if ($cart->invoiceLocation) {
            if (CountryEnum::BELGIUM === $cart->invoiceLocation->country) {
                return true;
            }
        } else {
            $userService = new UserService(user: $cart->user);
            if ($userService->hasVAT()) {
                return true;
            }
        }

        return false;
    }
}
