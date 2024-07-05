<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\UpdateItemCart;
use App\Models\Item;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendMailUpdateItemCart implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public User $user, public Item $item) {}

    public function handle(): void
    {
        Mail::to(users: $this->user)
            ->send(mailable: new UpdateItemCart(item: $this->item, user: $this->user));
    }
}
