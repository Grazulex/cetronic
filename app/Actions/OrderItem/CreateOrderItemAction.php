<?php

declare(strict_types=1);

namespace App\Actions\OrderItem;

use App\DataObjects\OrderItem\OrderItemDataObject;
use App\Models\OrderItem;

final class CreateOrderItemAction
{
    public function handle(OrderItemDataObject $dataObject): OrderItem
    {
        return OrderItem::create(attributes: $dataObject->toArray());
    }
}
