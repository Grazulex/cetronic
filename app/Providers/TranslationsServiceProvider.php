<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Outhebox\LaravelTranslations\LaravelTranslationsApplicationServiceProvider;

final class TranslationsServiceProvider extends LaravelTranslationsApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();
    }

    protected function gate(): void
    {
        Gate::define('viewLaravelTranslationsUI', function ($user) {
            return in_array($user->email, [
                'admin@jnkconsult.be',
                'info@cetronicbenelux.com',
                'bhamani.geoffrey@gmail.com',
                'geoffrey@cetronicbenelux.com'
            ]);
        });
    }
}
