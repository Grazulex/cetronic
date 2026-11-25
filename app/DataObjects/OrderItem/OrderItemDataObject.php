<?php

declare(strict_types=1);

namespace App\DataObjects\OrderItem;

use App\Models\CartItem;
use App\Models\Order;
use App\Services\ItemService;

final readonly class OrderItemDataObject
{
    public function __construct(
        private Order    $order,
        private CartItem $cartItem,
    ) {}

    public function toArray(): array
    {
        return [
            'order_id' => $this->order->id,
            'item_id' => $this->cartItem->item_id,
            'name' => $this->cartItem->item->reference,
            'quantity' => $this->cartItem->quantity,
            'price_old' => $this->cartItem->price_old,
            'price_paid' => ($this->cartItem->price_promo > 0) ? $this->cartItem->price_promo : $this->cartItem->price,
            'price_show' => ($this->cartItem->price_promo > 0) ? $this->cartItem->price : $this->cartItem->price_promo,
            'promo_type' => (new ItemService($this->cartItem->item))->promoType($this->order->user),
        ];
    }
}
