<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\Cart;
use App\Services\CartService;
use App\Services\GuestService;
use App\Services\UserService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;

final class TotalCart extends Component
{
    public Cart $cart;

    protected $listeners = ['total_updated' => 'render'];

    public function mount(): void
    {
    }

    public function render(GuestService $guestService, CartService $cartService): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        if ( ! auth()->check()) {
            $user = $guestService->getGuestUser();
        } else {
            $user = auth()->user();
        }
        $userService = new UserService($user);
        $total = $cartService->getTotal($this->cart);
        $shippingPrice = $cartService->getShippingTotal();
        $franco = $userService->getFranco();
        $fixeShipping = $userService->getFixedShippingPrice();
        $hasVAT = $cartService->needVAT($this->cart);
        $VAT = $cartService->getVAT($this->cart);
        $shippingVAT = $cartService->getShippingVAT();
        $totalVAT = $cartService->getTotalVAT($this->cart);
        $shippingTotalVAT = $cartService->getShippingTotalVAT();
        $discount = $cartService->getDiscount($this->cart);

        return view('livewire.total-cart', compact('total', 'shippingPrice', 'franco', 'hasVAT', 'VAT', 'totalVAT', 'discount', 'shippingVAT', 'shippingTotalVAT', 'fixeShipping'));
    }
}
