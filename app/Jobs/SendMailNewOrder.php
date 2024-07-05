<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\NewOrder;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendMailNewOrder implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        if ($this->order->user->agent && $this->order->user->agent->email) {
            $cc = [config(key: 'mail.from.address'),'geoffrey@cetronicbenelux.com', $this->order->user->agent->email];
        } else {
            $cc = [config(key: 'mail.from.address'),'geoffrey@cetronicbenelux.com'];
        }

        Mail::to(users: $this->order->user)
            ->cc(users: $cc)
            ->send(mailable: new NewOrder(order: $this->order));
    }
}
