<?php

declare(strict_types=1);

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Facade;

return [

    'available_locales' => [
        'en',
        'fr',
        'nl'
    ],

    'providers' => ServiceProvider::defaultProviders()->merge([
        Intervention\Image\ImageServiceProvider::class,
        /*
         * Laravel Framework Service Providers...
         */

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\TranslationsServiceProvider::class,
        App\Providers\HorizonServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\CookiesServiceProvider::class,
    ])->toArray(),

    'aliases' => Facade::defaultAliases()->merge([
        'Image' => Intervention\Image\Facades\Image::class,

        // 'ExampleClass' => App\Example\ExampleClass::class,
    ])->toArray(),

];
