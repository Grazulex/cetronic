<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;

final class FormCart extends Component
{
    public CartItem $cartItem;

    public array $quantity = [0];

    public function mount(): void
    {
        if ($this->cartItem->item->multiple_quantity && ($this->cartItem->quantity % $this->cartItem->item->multiple_quantity)) {
            $this->quantity[$this->cartItem->id] = $this->cartItem->item->multiple_quantity;
            $this->cartItem->update(['quantity' => $this->cartItem->item->multiple_quantity]);
        } else {
            $this->quantity[$this->cartItem->id] = $this->cartItem->quantity;
        }
    }

    public function render(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view(view: 'livewire.form-cart');
    }

    public function updateCart(CartService $cartService): void
    {
        $cartService->updateCart(cartItem: $this->cartItem, quantity: (int) $this->quantity[$this->cartItem->id]);

        $this->dispatch('cart_updated');
        $this->dispatch('quantity_updated');
        $this->dispatch('total_updated');

        $this->dispatch('alert',
            type: 'success',
            message: __(key: 'cart.item.added')
        );
    }

    public function removeItem(CartService $cartService): void
    {
        $cartService->removeItem(cartItem: $this->cartItem);

        $this->dispatch('cart_updated');
        $this->dispatch('total_updated');

        $this->dispatch('alert',
            type: 'success',
            message: __(key: 'toast.item.removed')
        );
    }
}
