<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\DataObjects\Order\OrderDataObject;
use App\Models\Order;

final class CreateOrderAction
{
    public function handle(OrderDataObject $dataObject): Order
    {
        return Order::create(attributes: $dataObject->toArray());
    }
}
