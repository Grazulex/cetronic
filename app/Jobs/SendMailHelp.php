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

    public function __construct(public array $data)
    {
    }

    public function handle(): void
    {
        Mail::to(users:'info@cetronicbenelux.com')
            ->cc(users:'admin@jnkconsult.be')
            ->send(mailable: new NewHelp(datas: $this->data));
    }
}
