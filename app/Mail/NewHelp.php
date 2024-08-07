<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class NewHelp extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(protected array $datas) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('emails.subjet.help.new'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.help.new',
            with: [
                'datas' => $this->datas,
            ],
        );
    }
}
