<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;

final class OrderObserver
{
    // NOTE: Le dispatch de SendMailNewOrder a été déplacé dans OrderService::create()
    // pour éviter une race condition où le job était exécuté AVANT la création des OrderItems.
    // Voir commit pour plus de détails.

    public function deleted(Order $order): void
    {
        $order->update(['reference' => $order->reference . '-DELETED']);
    }
}
