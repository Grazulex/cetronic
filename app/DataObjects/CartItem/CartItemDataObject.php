<?php

declare(strict_types=1);

namespace App\DataObjects\CartItem;

use App\Models\Cart;
use App\Models\Item;

final readonly class CartItemDataObject
{
    public function __construct(
        private Item   $item,
        private Cart   $cart,
        private float  $price,
        private float  $price_old = 0,
        private float  $price_promo = 0,
        private int    $quantity = 1,
        private string $variant = '',
    ) {
    }

    public function toArray(): array
    {
        return [
            'item_id' => $this->item->id,
            'cart_id' => $this->cart->id,
            'price_old' => $this->price_old,
            'price' => $this->price,
            'price_promo' => $this->price_promo,
            'quantity' => $this->quantity,
            'variant' => $this->variant,
        ];
    }
}
