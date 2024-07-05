<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\Item;
use App\Services\CartService;
use App\Services\ItemService;
use App\Services\UserService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Redirect;
use Livewire\Component;

final class FormItem extends Component
{
    public Item $item;

    public array $quantity = [0];
    public array $variant = [''];

    public function mount(): void
    {
        $this->quantity[$this->item->id] = $this->item->multiple_quantity;
        $meta = $this->item->category->metas->where(key: 'is_choice', value: true)->first();
        if ($meta) {
            $this->variant[$this->item->id] = $this->item->metas->where(key: 'meta_id', value: $meta->id)->first()->value ?? '';
        } else {
            $this->variant[$this->item->id] = '';
        }
    }

    public function render(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view(view: 'livewire.form-item');
    }

    public function down(): void
    {
        if ($this->quantity[$this->item->id] > $this->item->multiple_quantity) {
            $this->quantity[$this->item->id] -= $this->item->multiple_quantity;
        }
    }

    public function up(): void
    {
        $this->quantity[$this->item->id] += $this->item->multiple_quantity;
    }

    public function addToCart(CartService $cartService)
    {
        if ( ! auth()->check()) {
            return Redirect::route(route: 'login')->with(key: 'error', value:  __(key: 'cart.login'));
        }

        $userService = new UserService(user: auth()->user());
        $openCart = $userService->getOpenCart();
        if ( ! $openCart) {
            $openCart = $cartService->creatNewCart(user: auth()->user());
        }

        $itemService = new ItemService(item: $this->item);
        $prices = $itemService->getPrice(user: (auth()->user()) ? auth()->user() : null);


        $cartService->addToCart(cart: $openCart, item:  $this->item, price: $prices['price_promo'], price_promo: $prices['price_end'], quantity: (int) $this->quantity[$this->item->id], price_old: $prices['price_start'], variant: $this->variant[$this->item->id]);

        $this->emit(event: 'cart_updated');

        if ($cartService->getTotal($openCart) < $userService->getFranco()) {
            $this->dispatchBrowserEvent(event: 'alert', data: [
                'type' => 'warning',
                'message' => __('cart.item.franco', ['franco' => $userService->getFranco()]),
            ]);
        } else {
            $this->dispatchBrowserEvent(event: 'alert', data: [
                'type' => 'success',
                'message' => __('cart.item.added'),
            ]);
        }
    }
}
