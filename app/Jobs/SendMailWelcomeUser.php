<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\WelcomeUser;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendMailWelcomeUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function handle(): void
    {
        Mail::to(users: $this->user)
            ->send(mailable: new WelcomeUser(user: $this->user));
    }
}
