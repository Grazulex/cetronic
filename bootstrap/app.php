<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        Intervention\Image\ImageServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(AppServiceProvider::HOME);

        $middleware->encryptCookies(except: [

        ]);
        $middleware->validateCsrfTokens(except: [

        ]);

        $middleware->throttleApi();

        $middleware->replace(Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class, App\Http\Middleware\PreventRequestsDuringMaintenance::class);
        $middleware->replace(Illuminate\Foundation\Http\Middleware\TrimStrings::class, App\Http\Middleware\TrimStrings::class);
        $middleware->replace(Illuminate\Http\Middleware\TrustHosts::class, App\Http\Middleware\TrustHosts::class);
        $middleware->replace(Illuminate\Http\Middleware\TrustProxies::class, App\Http\Middleware\TrustProxies::class);

        $middleware->alias([
            'localeCookieRedirect' => Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect::class,
            'localeSessionRedirect' => Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            'localeViewPath' => Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath::class,
            'localizationRedirect' => Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
            'localize' => Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (Throwable $e): void {
            if (app()->bound('honeybadger')) {
                app('honeybadger')->notify($e, app('request'));
            }
        });
    })->create();
