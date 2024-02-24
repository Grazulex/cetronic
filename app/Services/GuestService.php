<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

final class GuestService
{
    public function getGuestUser(): User
    {
        return User::where('id', '=', 2)->first();
    }

    public function setCookie(string $name): string
    {
        $cookie = Str::random(32);
        Cookie::queue($name, $cookie, 60 * 24 * 30);

        return $cookie;
    }

    public function getCookie($name): string|array|null
    {
        return Cookie::get($name);
    }

    public function forgetCookie($name): void
    {
        Cookie::queue(Cookie::forget($name));
    }
}
