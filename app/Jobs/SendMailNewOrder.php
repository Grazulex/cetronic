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
        // Envoyer l'email au client
        Mail::to(users: $this->order->user)
            ->send(mailable: new NewOrder(order: $this->order));

        // Envoyer une copie séparée aux adresses Cetronic
        $cetronicEmails = [
            config(key: 'mail.from.address'),
            'geoffrey@cetronicbenelux.com',
        ];

        foreach ($cetronicEmails as $email) {
            Mail::to(users: $email)
                ->send(mailable: new NewOrder(order: $this->order));
        }

        // Envoyer à l'agent si présent
        if ($this->order->user->agent && $this->order->user->agent->email) {
            Mail::to(users: $this->order->user->agent->email)
                ->send(mailable: new NewOrder(order: $this->order));
        }
    }
}
