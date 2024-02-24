<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\SendMailActifUser;
use App\Jobs\SendMailNewUser;
use App\Models\User;

final class UserObserver
{
    public function creating(User $user): void
    {
        $user->password = bcrypt(rand(100000, 999999));
    }

    public function created(User $user): void
    {
        SendMailNewUser::dispatch($user);
    }

    public function updating(User $user): void
    {
        if ($user->isDirty('is_actif') && $user->is_actif) {
            SendMailActifUser::dispatch($user);
        }
    }
}
