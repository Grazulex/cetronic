<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\CartItem;
use App\Services\UserService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;

final class PriceCart extends Component
{
    public CartItem $cartItem;

    protected $listeners = ['quantity_updated' => 'render'];

    public function mount(): void {}

    public function render(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        $price_start = $this->cartItem->price_old;
        $price_promo = $this->cartItem->price;
        $price_end = $this->cartItem->price_promo;

        if (auth()->check()) {
            $discount = (new UserService(user: auth()->user()))->getBrandAndCategoryDiscounts(brand: $this->cartItem->item->brand, category: $this->cartItem->item->category, quantity: $this->cartItem->quantity);
            if ($discount) {
                $price_end = $price_end - ($price_end * ($discount->reduction / 100));
            }
        }

        return view(view: 'livewire.price-cart', data: compact('price_promo', 'price_start', 'price_end'));
    }
}
