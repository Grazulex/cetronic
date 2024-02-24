<?php

declare(strict_types=1);

namespace App\Actions\CartItem;

use App\DataObjects\CartItem\CartItemDataObject;
use App\Models\CartItem;

final class CreateCartItemAction
{
    public function handle(CartItemDataObject $dataObject): CartItem
    {
        return CartItem::create(attributes: $dataObject->toArray());
    }
}
