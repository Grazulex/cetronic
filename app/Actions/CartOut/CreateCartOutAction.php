<?php

declare(strict_types=1);

namespace App\Actions\CartOut;

use App\DataObjects\CartOut\CartOutDataObject;
use App\Models\CartOut;

final class CreateCartOutAction
{
    public function handle(CartOutDataObject $dataObject): CartOut
    {
        return CartOut::create(attributes: $dataObject->toArray());
    }
}
