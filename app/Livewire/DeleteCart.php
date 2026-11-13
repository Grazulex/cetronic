<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;

final class DeleteCart extends Component
{
    public CartItem $cartItem;

    public function mount(): void {}

    public function render(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view(view: 'livewire.delete-cart');
    }

    public function removeItem(CartService $cartService)
    {
        $cartService->removeItem(cartItem: $this->cartItem);

        if (0 === $cartService->getCartCount(cart: $this->cartItem->cart)) {
            $cartService->deleteCart(cart: $this->cartItem->cart);
            $this->dispatch('alert',
                type: 'success',
                message: __(key: 'toast.cart.removed')
            );

            return redirect()->route(route: 'home');
        }
        $this->redirectRoute('cart'); // hotfix to avoid 404 error
        $this->dispatch('cart_updated');
        $this->dispatch('total_updated');

        $this->dispatch('alert',
            type: 'success',
            message: __('toast.item.removed')
        );

        $this->dispatch('cart-item-deleted',
            cart_line_id: $this->cartItem->id
        );

    }
}
