<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\NewHelp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendMailHelp implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public array $data) {}

    public function handle(): void
    {
        // Envoyer à chaque destinataire séparément pour un meilleur suivi
        $recipients = [
            'info@cetronicbenelux.com',
            'admin@jnkconsult.be',
        ];

        foreach ($recipients as $email) {
            Mail::to(users: $email)
                ->send(mailable: new NewHelp(datas: $this->data));
        }
    }
}
