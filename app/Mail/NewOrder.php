<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

final class NewOrder extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected Order $order,
        protected ?string $pdfContent = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans(key: 'emails.order_new.subject') . $this->order->reference,
        );
    }

    public function content(): Content
    {
        App::setLocale(locale: $this->order->user->language);

        return new Content(
            view: 'emails.order.new',
            with: [
                'reference' => $this->order->reference,
                'name' => $this->order->user->name,
                'locale' => $this->order->user->language,
            ],
        );
    }

    public function attachments(): array
    {
        // Utiliser le PDF pré-généré si disponible, sinon le générer
        if ($this->pdfContent === null) {
            $orderService = new OrderService();
            $this->pdfContent = $orderService->getPdf(order: $this->order);
        }

        return [
            Attachment::fromData(fn() => $this->pdfContent, name: $this->order->reference . '.pdf')
                ->withMime(mime: 'application/pdf'),
        ];
    }
}
