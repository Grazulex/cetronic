<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\ActifUser;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendMailActifUser implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param int $userId L'ID du user (au lieu du modèle complet pour éviter les erreurs de sérialisation)
     */
    public function __construct(public int $userId) {}

    public function handle(): void
    {
        // Recharger le user depuis la DB
        $user = User::find($this->userId);

        if (!$user) {
            \Log::warning("User {$this->userId} not found for SendMailActifUser");
            return;
        }

        Mail::to(users: $user)
            ->send(mailable: new ActifUser(user: $user));
    }
}
