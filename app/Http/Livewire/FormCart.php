<?php

declare(strict_types=1);

namespace App\Http\Livewire;

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

        $this->emit(event: 'cart_updated');
        $this->emit(event: 'quantity_updated');
        $this->emit(event: 'total_updated');

        $this->dispatchBrowserEvent(event: 'alert', data: [
            'type' => 'success',
            'message' => __(key: 'cart.item.added'),
        ]);
    }

    public function removeItem(CartService $cartService): void
    {
        $cartService->removeItem(cartItem: $this->cartItem);

        $this->emit(event: 'cart_updated');
        $this->emit(event: 'total_updated');

        $this->dispatchBrowserEvent(event: 'alert', data: [
            'type' => 'success',
            'message' => __(key: 'toast.item.removed'),
        ]);
    }
}
