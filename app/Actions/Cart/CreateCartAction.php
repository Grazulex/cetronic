<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\DataObjects\Cart\CartDataObject;
use App\Models\Cart;

final class CreateCartAction
{
    public function handle(CartDataObject $dataObject): Cart
    {
        return Cart::create(attributes: $dataObject->toArray());
    }
}
