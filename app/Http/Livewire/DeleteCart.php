<?php

declare(strict_types=1);

namespace App\Http\Livewire;

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
            $this->dispatchBrowserEvent(event: 'alert', data: [
                'type' => 'success',
                'message' => __(key: 'toast.cart.removed'),
            ]);

            return redirect()->route(route: 'home');
        }
        $this->redirectRoute('cart'); // hotfix to avoid 404 error
        $this->emit(event: 'cart_updated');
        $this->emit(event: 'total_updated');

        $this->dispatchBrowserEvent(event: 'alert', data: [
            'type' => 'success',
            'message' => __('toast.item.removed'),
        ]);

        $this->dispatchBrowserEvent('cart-item-deleted', [
            'cart_line_id' => $this->cartItem->id,
        ]);

    }
}
