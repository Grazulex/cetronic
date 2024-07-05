<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class WelcomeUser extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(protected User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans(key: 'emails.user_welcome.subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user.welcome',
            with: [
                'name' => $this->user->name,
            ],
        );
    }
}
