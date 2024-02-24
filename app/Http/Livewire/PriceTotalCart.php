<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\CartItem;
use App\Services\UserService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;

final class PriceTotalCart extends Component
{
    public CartItem $cartItem;

    protected $listeners = ['quantity_updated' => 'render'];

    public function mount(): void
    {
    }

    public function render(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        $price = $this->cartItem->price;
        if ($this->cartItem->price_promo > 0) {
            $price = $this->cartItem->price_promo;
        }

        if (auth()->check()) {
            $discount = (new UserService(user: auth()->user()))->getBrandAndCategoryDiscounts(brand: $this->cartItem->item->brand, category: $this->cartItem->item->category, quantity: $this->cartItem->quantity);
            if ($discount) {
                $price = $price - ($price * ($discount->reduction / 100));
            }
        }

        $totalPrice = $price * $this->cartItem->quantity;

        return view(view: 'livewire.priceTotal-cart', data: compact('totalPrice'));
    }
}
