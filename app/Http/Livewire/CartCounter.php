<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Services\CartService;
use App\Services\UserService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;

final class CartCounter extends Component
{
    protected $listeners = ['cart_updated' => 'render'];

    public function render(CartService $cartService): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        $counter = 0;
        if (auth()->check()) {
            $userService = new UserService(user: auth()->user());
            $openCart = $userService->getOpenCart();
            if ($openCart) {
                $counter = $cartService->getCartCount(cart: $openCart);
            }
        }


        return view(view: 'livewire.cart-counter', data: compact('counter'));
    }
}
