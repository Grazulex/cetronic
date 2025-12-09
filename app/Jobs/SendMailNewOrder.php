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
use Illuminate\Support\Facades\Log;
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
        // Augmenter les limites pour la génération de PDF
        ini_set('max_execution_time', '300'); // 5 minutes
        ini_set('memory_limit', '512M'); // 512 MB de mémoire

        // Forcer le rechargement de la commande et de ses items depuis la DB
        // pour éviter tout problème de cache ou de sérialisation
        $this->order->refresh();
        $this->order->load('items', 'user');

        // Vérification de sécurité: s'assurer que les items existent
        if ($this->order->items->isEmpty()) {
            Log::error("SendMailNewOrder: Commande {$this->order->reference} n'a pas d'items!");
            throw new \RuntimeException("Order {$this->order->reference} has no items - cannot send confirmation email");
        }

        // Générer le PDF une seule fois pour tous les emails
        $orderService = new \App\Services\OrderService();
        $pdfContent = $orderService->getPdf(order: $this->order);

        // Envoyer l'email au client
        Mail::to(users: $this->order->user)
            ->send(mailable: new NewOrder(order: $this->order, pdfContent: $pdfContent));

        // Envoyer une copie séparée aux adresses Cetronic
        $cetronicEmails = [
            config(key: 'mail.from.address'),
            'geoffrey@cetronicbenelux.com',
        ];

        foreach ($cetronicEmails as $email) {
            Mail::to(users: $email)
                ->send(mailable: new NewOrder(order: $this->order, pdfContent: $pdfContent));
        }

        // Envoyer à l'agent si présent
        if ($this->order->user->agent && $this->order->user->agent->email) {
            Mail::to(users: $this->order->user->agent->email)
                ->send(mailable: new NewOrder(order: $this->order, pdfContent: $pdfContent));
        }
    }
}
