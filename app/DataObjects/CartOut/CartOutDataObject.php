<?php

declare(strict_types=1);

namespace App\DataObjects\CartOut;

use App\Models\Item;
use App\Models\User;

final readonly class CartOutDataObject
{
    public function __construct(
        private Item $item,
        private User $user,
        private int  $quantity = 1,
    ) {}

    public function toArray(): array
    {
        return [
            'item_id' => $this->item->id,
            'user_id' => $this->user->id,
            'quantity' => $this->quantity,
        ];
    }
}
