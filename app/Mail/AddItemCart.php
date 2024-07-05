<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Item;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class AddItemCart extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(protected Item $item, protected User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans(key: 'emails.cart_add.subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cart.addItem',
            with: [
                'product' => $this->item->category->name . ' - ' . $this->item->brand->name . ' (' . $this->item->reference . ')',
                'name' => $this->user->name,
            ],
        );
    }
}
