<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\SendMailNewOrder;
use App\Models\Order;

final class OrderObserver
{
    public function created(Order $order): void
    {
        SendMailNewOrder::dispatch($order);
    }

    public function deleted(Order $order): void
    {
        $order->update(['reference' => $order->reference.'-DELETED']);
    }
}
