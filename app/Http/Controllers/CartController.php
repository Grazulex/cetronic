<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enum\CartStatusEnum;
use App\Models\Cart;
use App\Services\CartService;
use App\Services\GuestService;
use App\Services\ItemService;
use App\Services\OrderService;
use App\Services\UserService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class CartController extends Controller
{
    public function index(): Application|Factory|View|\Illuminate\Foundation\Application
    {
        $cartLines = null;
        if (auth()->check()) {
            $userService = new UserService(user: auth()->user());
            $cartService = new CartService();
            $openCart = $userService->getOpenCart();
        } else {
            $guestService = new GuestService();
            $guest = $guestService->getGuestUser();
            $userService = new UserService($guest);
            $cartService = new CartService();
            $cookie = $guestService->getCookie(name: 'cetronic_cart');
            if ( ! $cookie) {
                $cookie = $guestService->setCookie(name: 'cetronic_cart');
            }
            $openCart = $userService->getOpenCart(cookie: $cookie);
        }
        if ($openCart) {
            $cartLines = $cartService->getCartContent(cart: $openCart);
        }

        if ($cartLines) {
            foreach ($cartLines as $cartLine) {
                if ($cartLine->item) {
                    $itemService = new ItemService($cartLine->item);
                    if (auth()->check()) {
                        $prices = $itemService->getPrice(auth()->user());
                    } else {
                        $prices = $itemService->getPrice();
                    }
                    $cartService->updatePrices(cart: $openCart, item: $cartLine->item, price: $prices['price_promo'], price_promo: $prices['price_end'], price_old: $prices['price_start'], variant: $cartLine->variant);
                }
            }
        }

        return view(view: 'front.pages.cart', data: compact('cartLines', 'openCart'));
    }

    public function store(Cart $cart): RedirectResponse
    {
        if (CartStatusEnum::SOLD !== $cart->status) {
            $orderService = new OrderService();
            $orderService->create(cart: $cart);
        }

        return redirect()->route(route: 'order.thanks')->with(key: 'success', value: 'profile successfully updated!');
    }

    public function confirm(Cart $cart): Application|Factory|View|\Illuminate\Foundation\Application
    {
        $cartService = new CartService();
        $cartLines = $cartService->getCartContent(cart: $cart);

        return view(view: 'front.pages.cart.confirm', data: compact('cart', 'cartLines'));
    }
}
