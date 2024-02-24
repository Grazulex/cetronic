<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\NewUser;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendMailNewUser implements ShouldQueue
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
        $to = ['geoffrey@cetronicbenelux.com',config('mail.from.address')];
        Mail::to(users: $to)
            ->send(mailable: new NewUser(user: $this->user));
    }
}
