<?php

declare(strict_types=1);

namespace App\Listeners;

use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\App;

final class UserLoginAt
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {}


    public function handle(Login $event): void
    {
        $event->user->update([
            'logged_at' => Carbon::now(),
        ]);
        App::setLocale(locale: $event->user->language);
    }
}
